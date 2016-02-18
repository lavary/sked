<?php

namespace Sked;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Finder\Finder;

class ScheduleCommand {

    /**
     * Run the command
     *
     * @return void
     */
     public static function run () {
        
        $config    = self::loadConfig();
        $taskFiles = self::collectFiles($config['src']); 
    
        foreach ($taskFiles as $key => $taskFile) {
                        
            $schedule = require $taskFile->getRealPath();            

            if( !$schedule instanceof Schedule) {
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
    public static function collectFiles($source) {
        
        $finder   = new Finder();
        $iterator = $finder->files()
                  ->name('*Tasks.php')
                  ->in($source);
        
        return $iterator;
    }


     /**
     * Collect the configuration options
     *
     * @return  array
     */
     public static function loadConfig() {
        
        $options               = self::parseOptions();
        $defaultConfigFileName = 'sked.yml';
        
        $config = self::parseConfig(__DIR__ . '/../' . $defaultConfigFileName);
        $config['src'] = __DIR__ . '/' . $config['src'];
        
        if (file_exists(__DIR__ . '/../../../../' . $defaultConfigFileName)) {
            $config = array_merge($config, self::parseConfig(__DIR__ . '/../../../../' . $defaultConfigFileName));
        }

        if (isset($options['configuration-file']) && file_exists($options['configuration-file'])) {
            $config = array_merge($config, self::parseConfig($options['configuration-file']));    
        }   

        return $config; 
            
     } 

     /**
     * Parse the YAML files
     *
     * @param  string $filename
     * @return  null|array
     */
     public static function parseConfig($filename) {
        
        $yaml = new Parser();
        
        try {
            return $yaml->parse(file_get_contents($filename));
        } catch (ParseException $e) {
            return null;
        }

     }

    /**
     * Parse the command options
     *
     * @return  array
     */
     public static function parseOptions () {
        
        return getopt('c:t:', ['configuration-file', 'tasks']);  

     }    
}