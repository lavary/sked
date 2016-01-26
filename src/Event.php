<?php

namespace Sked;

use Closure;
use Carbon\Carbon;
use LogicException;
use Cron\CronExpression;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Contracts\Mail\Mailer;
use Symfony\Component\Process\Process;
use Illuminate\Console\Scheduling\Event as BaseEvent;

class Event extends BaseEvent
{

    /**
     * Current working directory
     *
     * @var string
     */
    protected $currentWorkingDirectory = null;

    /**
     * Determine if the filters pass for the event.
     *
     * @param  Sked\Invoker $invoker
     * @return bool
     */
    protected function filtersPass(Invoker $invoker)
    {
        if (($this->filter && ! $invoker->call($this->filter)) ||
             $this->reject && $invoker->call($this->reject)) {
            return false;
        }

        return true;
    }

    /**
      * Run the given event.
      *
      * @param  \Sked\Invoker  $invoker
      * @return void
      */
    public function run(Invoker $invoker)
    {

        if (count($this->afterCallbacks) > 0 || count($this->beforeCallbacks) > 0) {
            $this->runCommandInForeground($invoker);
        } else {
            $this->runCommandInBackground();
        }
    }

    /**
     * Change current working directory
     *
     * @param  string $directory
     * @return Sked\Event
     */
    public function cd($directory)
    {
        $this->currentWorkingDirectory = $directory;

        return $this;
    }

    /**
     * Change current working directory
     *
     * @param  string $directory
     * @return Sked\Event
     */
    protected function changeWorkingDirectory($directory = null)
    {
        if (is_null($directory)) {
            return chdir(__DIR__ . '/../../../../');
        }

        chdir($directory);
    }

    /**
     * Run the command in the background using exec.
     *
     * @return void
     */
    protected function runCommandInBackground()
    {
        $this->changeWorkingDirectory($this->currentWorkingDirectory);

        exec($this->buildCommand());
    }

    /**
     * Run the command in the foreground.
     *
     * @param  \Sked\Invoker $invoker
     * @return void
     */
    protected function runCommandInForeground(Invoker $invoker)
    {
        $this->callBeforeCallbacks($invoker);

        (new Process(
            trim($this->buildCommand(), '& ')
        ))->run();

        $this->callAfterCallbacks($invoker);
    }

    /**
     * Call all of the "before" callbacks for the event.
     *
     * @param  \Sked\Invoker $invoker
     * @return void
     */
    protected function callBeforeCallbacks(Invoker $invoker)
    {
        foreach ($this->beforeCallbacks as $callback) {
            $invoker->call($callback);
        }
    }

    /**
     * Call all of the "after" callbacks for the event.
     *
     * @param  \Sked\Invoker $invoker
     * @return void
     */
    protected function callAfterCallbacks(Invoker $invoker)
    {
        foreach ($this->afterCallbacks as $callback) {
            $invoker->call($callback);
        }
    }

    /**
     * Build the comand string.
     *
     * @return string
     */
    public function buildCommand()
    {
        $redirect = $this->shouldAppendOutput ? ' >> ' : ' > ';

        if ($this->withoutOverlapping) {
            $command = '(touch '. $this->mutexPath() . '; ' . $this->command . '; rm ' . $this->mutexPath() . ')' . $redirect . $this->output . ' 2>&1 &';
        } else {
            $command = $this->command . $redirect . $this->output . ' 2>&1 &';
        }

        echo $command;
        return $this->user ? 'sudo -u ' . $this->user . ' ' . $command : $command;
    }

    /**
     * Get the mutex path for the scheduled command.
     *
     * @return string
     */
    protected function mutexPath()
    {
        return sys_get_temp_dir() . '/schedule-' . md5($this->expression . $this->command);
    }

    /**
     * Determine if the given event should run based on the Cron expression.
     * @param  Sked\Caller $app
     * @return bool
     */
    public function isDue(Invoker $invoker)
    {
        return $this->expressionPasses() && $this->filtersPass($invoker);
    }

}