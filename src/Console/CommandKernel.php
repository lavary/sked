<?php

namespace Sked\Console;

use Symfony\Component\Console\Application as SymfonyApplication;

class CommandKernel extends SymfonyApplication
{

    /**
     * List of commands to register
     *
     * @var array
     */
    protected $commands = [
        
        \Sked\Console\Commands\ScheduleRunCommand::class,
        \Sked\Console\Commands\TaskGeneratorCommand::class,
    ];

    /**
     * Instantiate the class
     *
     */
    public function __construct($appName, $appVersion)
    {
        parent::__construct($appName, $appVersion);

        foreach($this->commands as $command) {
            $this->add(new $command);
        }
    }

    
    /**
     * Run the command
     *
     * @param array $arguments
     */
    public function handle()
    {    
        $this->run();
    }

}