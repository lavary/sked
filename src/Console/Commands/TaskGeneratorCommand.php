<?php

namespace Sked\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Finder\Finder;

class TaskGeneratorCommand extends Command
{

    /**
     * Stub content
     *
     * @var string
     */
    protected $stub;

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
        
        'frequency'   => 'everyThirtyMinutes',
        'constraint'  => 'weekdays',
        'in'          => 'path/to/your/command',
        'run'         => 'command/to/execute',
        'description' => 'Task description',
        'type'        => 'basic',
        'output'      => __DIR__ . '/../../../../../../Tasks',
    ];

    /**
     * Configures the current command
     *
     */
     protected function configure()
     {
        $this->setName('make:task')
             ->setDescription('Generate a task stub')
             ->setDefinition([

                new InputArgument('taskfile',        InputArgument::REQUIRED,   'The task file name'),               
                
                new InputOption('frequency',    'f',  InputOption::VALUE_OPTIONAL,   'The task\'s frequency',   array_get($this->defaults, 'frequency')),
                new InputOption('constraint',   'c',  InputOption::VALUE_OPTIONAL,   'The task\'s constraint',  array_get($this->defaults, 'constraint')),
                new InputOption('in',           'i',  InputOption::VALUE_OPTIONAL,   'The command\'s path',     array_get($this->defaults, 'in')),
                new InputOption('run',          'r',  InputOption::VALUE_OPTIONAL,   'The task\'s command',     array_get($this->defaults, 'run')),
                new InputOption('description',  'd',  InputOption::VALUE_OPTIONAL,   'The task\'s description', array_get($this->defaults, 'description')),
                new InputOption('output',       'o',  InputOption::VALUE_OPTIONAL,   'The output file path',    array_get($this->defaults, 'output')),
                new InputOption('type',         't',  InputOption::VALUE_OPTIONAL,   'The task type',           array_get($this->defaults, 'type')),

            ])
            ->setHelp('This command makes a task stub for you to work on');
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
        $this->stub      = $this->getStub();
        
        if ($this->stub) {
        
            $this->replaceFrequency()
                 ->replaceConstraint()
                 ->replaceCommand()
                 ->replacePath()
                 ->replaceDescription();

            if (file_put_contents($this->outputFile(), $this->stub)) {
                
               $output->writeln('<info>The task file generated successfully</info>');

            }

            exit();
              
        } 

        $output->writeln('There was a problem when generating the file. Please check your command.');
        exit();

    }  

    /**
     * Populate the output filename
     *
     * @return string
     */
    protected function outputFile()
    {
       return rtrim(array_get($this->options, 'output'), '/') . '/' . preg_replace('/Tasks|\.php$/', '', array_get($this->arguments, 'taskfile')) . 'Tasks.php';
    }

    /**
     * Get the task stub
     *
     * @return string
     */
    protected function getStub()
    {
        return file_get_contents(__DIR__ . '/../../Stubs/' . ucfirst($this->type() . '.php'));
    }

    /**
     * Get the task type
     *
     * @return string
     */
    protected function type()
    {
        return array_get($this->options, 'type');
    }


    /**
     * Replace frequency
     *
     * @return void
     */
    protected function replaceFrequency()
    {
        $this->stub = str_replace('DummyFrequency', rtrim(array_get($this->options, 'frequency'), '()'), $this->stub);
        return $this;
    }

    /**
     * Replace constraint
     *
     * @return void
     */
    protected function replaceConstraint()
    {
        $this->stub = str_replace('DummyConstraint', rtrim(array_get($this->options, 'constraint'), '()'), $this->stub);
        return $this;
    }

    /**
     * Replace command
     *
     * @return void
     */
    protected function replaceCommand()
    {
        $this->stub = str_replace('DummyCommand', array_get($this->options, 'run'), $this->stub);
        return $this;
    }

    /**
     * Replace path
     *
     * @return void
     */
    protected function replacePath()
    {
        $this->stub = str_replace('DummyPath', array_get($this->options, 'in'), $this->stub);
        return $this;
    }

    /**
     * Replace description
     *
     * @return void
     */
    protected function replaceDescription()
    {
        $this->stub = str_replace('DummyDescription', array_get($this->options, 'description'), $this->stub);
        return $this;
    }
    
      
}