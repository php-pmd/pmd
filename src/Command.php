<?php

namespace PhpPmd\Pmd;

class Command
{
    protected $argv;

    public function __construct($argv)
    {
        $this->argv = $argv;
    }

    public function parser()
    {
        $argv = $this->argv;
        $argv1 = $argv[1] ?? '';
        if ($argv1) {
            if (in_array($argv1, ['-v', '--version'])) {
                return [
                    'opcode' => 'version',
                ];
            } elseif (in_array($argv1, ['start', 'stop', 'restart'])) {
                if ($argv1 == 'stop') {
                    return [
                        'opcode' => $argv1
                    ];
                } else {
                    return [
                        'opcode' => $argv1,
                        'options' => $this->options($argv)
                    ];
                }
            }
        }
        return [
            'opcode' => 'help',
            'data' => $this->help(),
        ];
    }

    protected function options($argv)
    {
        return [
            'user' => $this->getArgvByName($argv, '-u') ?? $this->getArgvByName($argv, '--user'),
            'pass' => $this->getArgvByName($argv, '-p') ?? $this->getArgvByName($argv, '--pass'),
            'port' => $this->getArgvByName($argv, '--port')
        ];
    }

    protected function getArgvByName($argv, $name)
    {
        return in_array($name, $argv) ? ($argv[array_search($name, $argv) + 1] ?? null) : null;
    }

    protected function help()
    {
        return <<<USAGE
<y>PMD Version</y>: <g>{{version}}</g> 
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
    }
}