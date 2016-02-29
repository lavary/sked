<?php

namespace Sked\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;

use Sked\Schedule;
use Sked\Invoker;

class ScheduleRunCommand extends Command
{
    /**
     * Command arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * Command options
     *
     * @var array
     */
    protected $options;

    /**
     * Default option values
     *
     * @var array
     */
    protected $defaults = [

        'src' => '/../../../../../../Tasks',
    ];

    /**
     * Configures the current command
     *
     */
     protected function configure()
     {
        $this->setName('schedule:run')
             ->setDescription('Start the scheduler')
             ->setDefinition([
                new InputOption('source', 's', InputOption::VALUE_OPTIONAL, 'Path to the task files',  __DIR__ . array_get($this->defaults, 'src')),
            ])
             ->setHelp('This command starts the scheduler');
     } 
   
    /**
     * Executes the current command
     *
     * @param use Symfony\Component\Console\Input\InputInterface $input
     * @param use Symfony\Component\Console\Input\OutputIterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {        
        $this->arguments = $input->getArguments();
        $this->options   = $input->getOptions();
        $src             = array_get($this->options, 'source');
        $task_files      = $this->collectFiles($src); 
    
        foreach ($task_files as $key => $taskFile) {
                        
            $schedule = require $taskFile->getRealPath();            
            if (!$schedule instanceof Schedule) {
                continue;
            } 

            $events = $schedule->dueEvents(new Invoker());
            foreach ($events as $event) {
                
                echo '[', date('Y-m-d H:i:s'), '] Running scheduled command: ', $event->getSummaryForDisplay(), PHP_EOL;
                echo $event->buildCommand(), PHP_EOL;
                echo '---', PHP_EOL;
                
                $event->run(new Invoker());
            }
        } 
    }
 
    /**
    * Collect all task files
    *
    * @param  string $source
    * @return Iterator
    */
    public static function collectFiles($source)
    {    
        $finder   = new Finder();
        $iterator = $finder->files()
                  ->name('*Tasks.php')
                  ->in($source);
        
        return $iterator;
    }
  
}