<?php

namespace Sked;

use Illuminate\Console\Scheduling\Schedule as BaseSchedule;
use Illuminate\Support\Collection;
use Symfony\Component\Process\ProcessUtils;
use Symfony\Component\Process\PhpExecutableFinder;

class Schedule extends BaseSchedule {

    /**
     * An alias for the command() method
     *
     * @param  string $command
     * @param  array  $parameters
     * @return \Sked\Event
     */
     public function task ($command, array $parameters = array()) {
        
        return $this->command($command);

     }    
    
    /**
     * Add a new command event to the schedule.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Sked\Event
     */
    public function command($command, array $parameters = array())
    {

        return $this->exec($command, $parameters);
    }

    /**
     * Add a new command event to the schedule.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Sked\Event
     */
    public function exec($command, array $parameters = [])
    {
        if (count($parameters)) {
            $command .= ' ' . $this->compileParameters($parameters);
        }

        $this->events[] = $event = new Event($command);

        return $event;
    }

    /**
     * Get all of the events on the schedule that are due.
     *
     * @param  \Sked\Invoker $invoker
     * @return array
     */
    public function dueEvents(Invoker $invoker)
    {
        
        return array_filter($this->events, function ($event) use ($invoker) {
            return $event->isDue($invoker);
        });
    }
}