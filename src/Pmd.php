<?php

namespace PhpPmd\Pmd;

use Exception;
use PhpPmd\Pmd\Di\Container;
use PhpPmd\Pmd\File\PidFile;
use PhpPmd\Pmd\File\ConfigFile;
use PhpPmd\Pmd\File\ProcessFile;
use PhpPmd\Pmd\Http\Template;
use PhpPmd\Pmd\Http\HttpServer;
use PhpPmd\Pmd\Log\Logger;
use PhpPmd\Pmd\Socket\SocketServer;
use React\ChildProcess\Process;
use React\EventLoop\Factory;

/**
 * Class PmdCommand
 * @package PhpPmd\Pmd
 */
class Pmd
{
    /**
     * @var Container $container
     */
    public static $container;
    protected static $version = 'v0.1.3';
    protected static $http_enable = false;
    protected static $local_ip = '';

    public static function run()
    {
        static::checkSapiEnv();
        static::initHomePath();
        static::initLogger();
        static::loadFunctions();
        static::setExceptionHandler();
        static::initFiles();
        static::getLocalIp();
        static::parseCommand();
        static::daemonize();
        static::initLoop();
        static::installSignal();
        static::start();
        static::loopRun();

    }

    protected static function initFiles()
    {
        static::injection('pidFile', function () {
            return new PidFile(PMD_HOME . '/pmd.pid');
        });
        static::injection('processFile', function () {
            return new ProcessFile(PMD_HOME . '/process.yaml');
        });
        static::injection('configFile', function () {
            return new ConfigFile(PMD_HOME . '/config.yaml');
        });
    }

    protected static function injection($id, $concrete)
    {
        if (!static::$container) static::$container = new Container();
        static::$container->injection($id, $concrete);
    }

    protected static function initLogger()
    {
        static::injection('logger', function () {
            return new Logger(PMD_HOME . '/pmd.log');
        });
    }

    protected static function initLoop()
    {
        static::injection('loop', Factory::create());
    }

    protected static function setExceptionHandler()
    {
        \set_error_handler(function ($code, $msg, $file, $line) {
            \logger()->error("$msg in file $file on line $line");
        });
        \set_exception_handler(function (\Throwable $throwable) {
            \logger()->error("{$throwable->getMessage()} in file {$throwable->getFile()} on line {$throwable->getLine()}");
            exit(0);
        });
    }

    protected static function initHomePath()
    {
        if (ENV == 'DEV') {
            define('PMD_HOME', __DIR__ . '/../tmp');
        } else {
            define('PMD_HOME', getenv('HOME') . DIRECTORY_SEPARATOR . '.pmd');
        }
        try {
            if (!is_dir(PMD_HOME)) {
                $res = mkdir(PMD_HOME, 0777, true);
                if (!$res) {
                    exit('Create ' . PMD_HOME . ' fail.' . PHP_EOL);
                }
            }
        } catch (Exception $exception) {
            exit($exception->getMessage() . PHP_EOL);
        }
        define('PMD_ROOT', __DIR__);
    }

    protected static function loadFunctions()
    {
        require_once __DIR__ . '/functions.php';
    }

    /**
     * Check sapi.
     *
     * @return void
     */
    protected static function checkSapiEnv()
    {
        // Only for cli.
        if (\PHP_SAPI !== 'cli') {
            exit("Only run in command line mode. \n");
        }
        if (\DIRECTORY_SEPARATOR === '\\') {
            exit("Only run in UNIX system or UNIX like system. \n");
        }
    }

    protected static function parseCommand()
    {
        $command = new Command($_SERVER['argv']);
        $opcode = $command->parser();
        $command = $opcode['opcode'];
        switch ($command) {
            case 'process':
                if (!\pidFile()->isRunning()) {
                    \logger()->writeln("PMD is not running.");
                    break;
                }
                $config = \configFile()->getContent();
                $socket = $config['socket'];
                \logger()->writeln("ip:\t\t<g>{$socket['ip']}</g>");
                \logger()->writeln("port:\t\t<g>{$socket['port']}</g>");
                \logger()->writeln("app_key:\t<g>{$socket['app_key']}</g>");
                \logger()->writeln("app_secret:\t<g>{$socket['app_secret']}</g>");
                break;
            case 'help':
                \logger()->writeln(str_replace('{{version}}', static::$version, $opcode['data']));
                break;
            case 'version':
                \logger()->writeln("PMD <g>" . static::$version . "</g>");
                break;
            case 'restart':
            case 'stop':
                if (!\pidFile()->isRunning()) {
                    \logger()->writeln("PMD is not running.");
                } else {
                    if (static::stop()) {
                        if ($opcode['opcode'] == 'restart') {
                            $command = 'start';
                        }
                    }
                }
                break;
            case 'start':
                if (\pidFile()->isRunning()) {
                    \logger()->writeln("PMD is already running.");
                    exit(0);
                }
                $command = 'start';
                break;
            default:
                break;
        }
        if ($command == 'start') {
            static::checkConfig($opcode['options']);
        } else {
            exit(0);
        }
    }

    protected static function daemonize()
    {
        \umask(0);
        $pid = \pcntl_fork();
        if (-1 === $pid) {
            exit('Fork fail' . PHP_EOL);
        } elseif ($pid > 0) {
            usleep(500000);
            exit(0);
        }
        if (-1 === \posix_setsid()) {
            exit("Setsid fail" . PHP_EOL);
        }
        // Fork again avoid SVR4 system regain the control of terminal.
        $pid = \pcntl_fork();
        if (-1 === $pid) {
            exit("Fork fail" . PHP_EOL);
        } elseif (0 !== $pid) {
            usleep(500000);
            exit(0);
        }
        \pidFile()->setContent(\posix_getpid());
    }

    protected static function installSignal()
    {
        $signalHandler = Pmd::class . '::signalHandler';
        // stop
        \loop()->addSignal(\SIGINT, $signalHandler);
        \loop()->addSignal(\SIGTERM, $signalHandler);
        \loop()->addSignal(\SIGHUP, $signalHandler);

        \loop()->addSignal(\SIGUSR1, $signalHandler);
        \loop()->addSignal(\SIGUSR2, $signalHandler);
        \loop()->addSignal(\SIGQUIT, $signalHandler);
        \loop()->addSignal(\SIGIO, $signalHandler);
    }

    public static function signalHandler($signal)
    {
        switch ($signal) {
            case \SIGINT:
            case \SIGTERM:
            case \SIGHUP:
                $allProcess = \socket()->getProcess()->getAllProcess();
                $start_time = time();
                /**
                 * @var Process $process
                 */
                foreach ($allProcess as $pid => $process) {
                    \loop()->addPeriodicTimer(0.3, function ($timer) use ($pid, $process, $start_time) {
                        if ($process->isRunning()) {
                            if (time() - $start_time >= 1) {
                                $process->terminate(SIGTERM);
                            } elseif (time() - $start_time >= 2) {
                                $process->terminate(SIGKILL);
                            } else {
                                $process->terminate(SIGINT);
                            }
                        } else {
                            \socket()->getProcess()->unsetProcess($pid);
                            \loop()->cancelTimer($timer);
                        }
                    });
                }
                \loop()->addPeriodicTimer(0.3, function ($timer) {
                    if (count(\socket()->getProcess()->getAllProcess()) == 0) {
                        \loop()->cancelTimer($timer);
                        \logger()->info("PMD stop success[<g>OK</g>].");
                        static::clearAll();
                    }
                });
                break;
            case \SIGUSR1:
            case \SIGQUIT:
            case \SIGUSR2:
            case \SIGIO:
            case \SIGPIPE:
                break;
        }
    }

    private static function clearAll()
    {
        \pidFile()->unlink();
        \logger()->close();
        \loop()->futureTick(function () {
            \loop()->stop();
        });
    }

    protected static function start()
    {
        static::startSocketServer();
        static::startHttpServer();
        $startSuccessMsg = "PMD start success[<g>OK</g>].";
        \logger()->writeln($startSuccessMsg);
        \logger()->logDump();
        \logger()->info($startSuccessMsg);
    }

    protected static function startSocketServer()
    {
        static::injection('socket', function () {
            $config = \configFile()->getContent();
            $socket_port = $config['socket']['port'] ?? 0;
            $ip = static::$local_ip;
            $socket = new SocketServer($socket_port);
            if ($socket_port == 0) {
                $socket_port = $socket->getPort();
                $config['socket'] = [
                    'name' => 'local',
                    'ip' => $ip,
                    'port' => $socket_port,
                    'app_key' => \uuid('k-'),
                    'app_secret' => uuid('s-'),
                ];
                $config['remote_socket']["{$ip}:{$socket_port}"] = $config['socket'];
                \configFile()->setContent($config);
            }
            return $socket;
        });
        \socket();
    }

    protected static function getLocalIp()
    {
        if (function_exists('exec')) {
            $preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
            exec("ifconfig", $out, $stats);
            if (!empty($out)) {
                if (isset($out[1]) && strstr($out[1], 'inet')) {
                    $tmpIp = explode(" ", trim($out[1]));
                    if (preg_match($preg, trim($tmpIp[1]))) {
                        static::$local_ip = trim($tmpIp[1]);
                        return true;
                    }
                }
            }
            \logger()->error('Ip not found.');
        } else {
            \logger()->error('Function exec not found.');
        }
        exit(0);
    }

    protected static function startHttpServer()
    {
        if (static::$http_enable) {
            static::injection('view', function () {
                return new Template(
                    PMD_ROOT . '/Http/view/',
                    [
                        'site_title' => 'PMD Console',
                        'version' => self::$version,
                        'local_ip' => static::$local_ip
                    ]
                );
            });
            static::injection('http', function () {
                $config = \configFile()->getContent();
                $http_port = $config['http']['port'] ?? 2021;
                return (new HttpServer($http_port))->server();
            });
            \http();
        }
    }

    protected static function loopRun()
    {
        \loop()->run();
    }

    protected static function stop()
    {
        $master_pid = \pidFile()->getContent();
        $master_pid && \posix_kill($master_pid, \SIGINT);
        $timeout = 3;
        $start_time = \time();
        while (1) {
            \usleep(10000);
            $master_is_alive = $master_pid && \posix_kill($master_pid, 0);
            if ($master_is_alive) {
                // Timeout?
                if (\time() - $start_time >= $timeout) {
                    \logger()->writeln("PMD stop fail[<r>BAD</r>].");
                    exit(0);
                }
                continue;
            }
            // Stop success.
            \logger()->writeln("PMD stop success[<g>OK</g>].");
            break;
        }
        return true;
    }

    protected static function checkConfig($options)
    {

        $enableRegx = function ($value) {
            return $value == '0' || $value == '1';
        };
        static::$http_enable = (int)static::getStdinValue(
            ["Enable HTTP server?\n", " [<g>0</g>] Disable\n", " [<g>1</g>] Enable\n", "Choose whether to enable Http server <g>(0)</g>:"],
            '0',
            $enableRegx
        );
        if (static::$http_enable) {
            $config = \configFile()->getContent();
            $http_config = $config['http'] ?? [];
            foreach ($http_config as $key => $value) {
                $http_config[$key] = $options[$key] ?? $value;
            }
            $userRegx = function ($value) {
                $result = preg_match('/^[a-zA-Z]{4,16}$/', $value);
                if (!$result) \logger()->writeln("The manager account must be <g>4-16</g> letters.");
                return $result;
            };
            if (!isset($http_config['user']) || $http_config['user'] == null || !$userRegx($http_config['user'])) {
                $http_config['user'] = static::getStdinValue(
                    'Please enter the manager account <g>(user)</g>:',
                    'user',
                    $userRegx
                );
            }
            $passRegx = function ($value) {
                $result = preg_match('/[a-zA-Z0-9]{6,16}$/', $value);
                if (!$result) \logger()->writeln("The manager password must start with a letter, <g>6-16</g> letters or numbers.");
                return $result;
            };
            if (!isset($http_config['pass']) || $http_config['pass'] == null || !$passRegx($http_config['pass'])) {
                $http_config['pass'] = static::getStdinValue(
                    'Please enter the manager password <g>(123456)</g>:',
                    123456,
                    $passRegx
                );
            }
            $portRegx = function ($value) {
                $result = $value > 1024 && $value < 65535;
                if (!$result) \logger()->writeln("The HTTP service port must be between <g>1024</g> and <g>65535</g>.");
                return $result;
            };
            if (!isset($http_config['port']) || $http_config['port'] == null || !$portRegx($http_config['port'])) {
                $http_config['port'] = (int)static::getStdinValue(
                    'Please enter the HTTP service port <g>(2021)</g>:',
                    2021,
                    $portRegx
                );
            }
            $config['http'] = $http_config;
            \configFile()->setContent($config);
        }
    }

    protected static function getStdinValue($tips, $default, $callback = null)
    {
        if ($callback == null) {
            $callback = function ($value) {
                return $value;
            };
        }
        $fs = true;
        do {
            if (is_string($tips)) \logger()->write($tips);
            if (is_array($tips)) foreach ($tips as $tip) \logger()->write($tip);
            if ($fs) $fs = false;
            $value = trim(fgets(STDIN));
            if ($value == '') $value = $default;
        } while (!$callback($value));
        return $value;
    }

}