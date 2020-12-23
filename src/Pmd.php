<?php

namespace PhpPmd\Pmd;

use Exception;
use PhpPmd\Pmd\Core\Di\Container;
use PhpPmd\Pmd\Core\File\ConfigFile;
use PhpPmd\Pmd\Core\File\PidFile;
use PhpPmd\Pmd\Core\File\ProcessFile;
use PhpPmd\Pmd\Core\Log\Logger;
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
    protected static $version = 'v0.0.1';
    protected static $pmdPid;
    protected static $pidFile;
    protected static $command;
    protected $options = [];

    public static function run()
    {
        static::checkSapiEnv();
        static::init();
        static::parseCommand();
        static::daemonize();
        static::installSignal();
        exit;
        static::checkConfigFile();
    }

    protected static function init()
    {
        static::initHomePath();
        static::initLogger();
        static::initLoop();
        static::loadFunctions();
        static::setErrorHandler();
        static::initFiles();
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
        static::injection('loop', new Factory());
    }

    protected static function setErrorHandler()
    {
//        \set_error_handler(function ($code, $msg, $file, $line) {
//            \logger()->writeln("$msg in file $file on line $line");
//        });
    }

    protected static function initHomePath()
    {
        define('DIR_SEP', DIRECTORY_SEPARATOR);
        if (TMP) {
            define('PMD_HOME', __DIR__ . '/../tmp');
        } else {
            define('PMD_HOME', getenv('HOME') . DIR_SEP . '.pmd');
        }
        try {
            if (!is_dir(PMD_HOME)) {
                $res = mkdir(PMD_HOME, 0777, true);
                if (!$res) {
                    throw new Exception('Create ' . PMD_HOME . ' fail.');
                }
            }
        } catch (Exception $exception) {
            exit($exception->getMessage());
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
        $version = static::$version;
        $usage = <<<USAGE
<y>PMD Version</y>: <g>{$version}</g> 
<y>Usage</y>: 
  pmd <g><command></g> <g>[option]</g>
<y>Description:</y>
  Process manager based on reactPHP.
<y>Commands</y>:
  <g>start</g>\t\tStart PMD.
  <g>restart</g>\tRestart PMD.
  <g>stop</g>\t\tStop PMD.
<y>Options</y>:
  <g>-u, --user</g>\tSet account.
  <g>-p, --pass</g>\tSet password.
      <g>--port</g>\tSet http service port.
  <g>-h, --help</g>\tDisplay help for the given command. 
  <g>-v, --version</g>\tDisplay this application version.
USAGE;
        $argv = $_SERVER['argv'];
        $argv1 = $argv[1] ?? '';
        if ($argv1) {
            if (in_array($argv1, ['-h', '--help'])) {
                \logger()->writeln($usage);
                exit(0);
            } elseif (in_array($argv1, ['-v', '--version'])) {
                \logger()->writeln("PMD <g>" . static::$version . "</g>");
                exit(0);
            } elseif (in_array($argv1, ['start', 'stop', 'restart'])) {
                if (in_array($argv1, ['stop', 'restart'])) {
                    if (!file_exists(static::$pidFile)) {
                        \logger()->writeln("Pmd is not running.");
                        exit(0);
                    } else {
                        $master_pid = \pidFile()->getContent();
                        $master_pid && \posix_kill($master_pid, SIGHUP);
                        $timeout = 3;
                        $start_time = \time();
                        while (1) {
                            $master_is_alive = $master_pid && \posix_kill($master_pid, 0);
                            if ($master_is_alive) {
                                // Timeout?
                                if (\time() - $start_time >= $timeout) {
                                    \logger()->writeln("PMD stop fail.");
                                    exit;
                                }
                                // Waiting amoment.
                                \usleep(10000);
                                continue;
                            }
                            // Stop success.
                            \logger()->writeln("PMD stop success.");
                            if ($argv1 === 'stop') {
                                exit(0);
                            }
                            break;
                        }
                    }
                    $argv1 = 'start';
                } else {
                    if (\pidFile()->exists() > 0) {
                        \logger()->writeln("Pmd is already running.");
                        exit(0);
                    }
                }
                static::$command = $argv1;
            } else {
                \logger()->writeln($usage);
                exit(0);
            }
        } else {
            \logger()->writeln($usage);
            exit(0);
        }
    }

    protected static function daemonize()
    {
        \umask(0);
        $pid = \pcntl_fork();
        if (-1 === $pid) {
            throw new Exception('Fork fail');
        } elseif ($pid > 0) {
            exit(0);
        }
        if (-1 === \posix_setsid()) {
            throw new Exception("Setsid fail");
        }
        // Fork again avoid SVR4 system regain the control of terminal.
        $pid = \pcntl_fork();
        if (-1 === $pid) {
            throw new Exception("Fork fail");
        } elseif (0 !== $pid) {
            exit(0);
        }
        \logger()->dump();
        \pidFile()->setContent(\posix_getpid());
    }

    protected static function installSignal()
    {
        $signalHandler = 'static::signalHandler';
        // stop
        \pcntl_signal(\SIGINT, $signalHandler, false);
        // stop
        \pcntl_signal(\SIGTERM, $signalHandler, false);
        // graceful stop
        \pcntl_signal(\SIGHUP, $signalHandler, false);
        // reload
        \pcntl_signal(\SIGUSR1, $signalHandler, false);
        // graceful reload
        \pcntl_signal(\SIGQUIT, $signalHandler, false);
        // status
        \pcntl_signal(\SIGUSR2, $signalHandler, false);
        // connection status
        \pcntl_signal(\SIGIO, $signalHandler, false);
        // ignore
        \pcntl_signal(\SIGPIPE, \SIG_IGN, false);
    }

    public static function signalHandler($signal)
    {
        \logger()->writeln($signal);
        \pidFile()->unlink();
        /*
        switch ($signal) {
            // Stop.
            case \SIGINT:
            case \SIGTERM:
                static::$_gracefulStop = false;
                static::stopAll();
                break;
            // Graceful stop.
            case \SIGHUP:
                static::$_gracefulStop = true;
                static::stopAll();
                break;
            // Reload.
            case \SIGQUIT:
            case \SIGUSR1:
                static::$_gracefulStop = $signal === \SIGQUIT;
                static::$_pidsToRestart = static::getAllWorkerPids();
                static::reload();
                break;
            // Show status.
            case \SIGUSR2:
                static::writeStatisticsToStatusFile();
                break;
            // Show connection status.
            case \SIGIO:
                static::writeConnectionsStatisticsToStatusFile();
                break;
        }
        */
    }

    protected static function startPmd()
    {

    }

    protected static function stopPmd()
    {

    }

    protected static function getPidFile()
    {

    }

    protected static function checkConfigFile()
    {
        $config_file = PMD_HOME . 'config.yaml';
        if (!file_exists($config_file)) {
            $account = static::getStdinValue(
                'Please enter the admin account <g>(user)</g>:',
                'user',
                function ($value) {
                    $result = preg_match('/^[a-zA-Z]{4,16}$/', $value);
                    if (!$result) static::writeln("Must be <g>4-16</g> letters.");
                    return $result;
                }
            );
            $password = static::getStdinValue(
                'Please enter the admin password <g>(123456)</g>:',
                '123456',
                function ($value) {
                    $result = preg_match('/[a-zA-Z0-9]{6,16}$/', $value);
                    if (!$result) static::writeln("Must start with a letter, <g>6-16</g> letters or numbers.");
                    return $result;
                }
            );
            $port = static::getStdinValue(
                'Please enter the http service port <g>(2345)</g>:',
                2345,
                function ($value) {
                    $result = $value > 1024 && $value < 65535;
                    if (!$result) static::writeln("Must be between <g>1024</g> and <g>65535</g>.");
                    return $result;
                }
            );
            var_dump($account, $password, $port);
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
            if ($fs) {
                static::write($tips);
                $fs = false;
            } else {
                static::write('Please enter again：');
            }
            $value = trim(fgets(STDIN));
            if ($value == '') $value = $default;
        } while (!$callback($value));
        return $value;
    }

    protected static function write($message)
    {
        \logger()->write(static::decorated($message));
    }

    protected static function writeln($message)
    {
        static::write($message . PHP_EOL);
    }

    protected static function decorated($msg)
    {
        $line = "\033[1A\n\033[K";
        $red = "\033[31m";
        $white = "\033[47;30m";
        $yellow = "\033[33m";
        $green = "\033[32;40m";
        $end = "\033[0m";
        $msg = \str_replace(array('<n>', '<r>', '<y>', '<w>', '<g>'), array($line, $red, $yellow, $white, $green), $msg);
        $msg = \str_replace(array('</n>', '</r>', '</y>', '</w>', '</g>'), $end, $msg);
        return $msg;
    }
}