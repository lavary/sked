# Sked

Create just one cron job once and for all, manage the rest right from the code.

[![Latest Unstable Version](https://poser.pugx.org/lavary/sked/v/unstable.svg)](https://packagist.org/packages/lavary/sked)

Sked is a framework-agnostic package for creating cron jobs using a fluent API. It's been built on top of the powerful [Laravel task scheduler](https://laravel.com/docs/master/scheduling), but the effort has been made to make Laravel Task Scheduler available to other environments and contexts, while providing additional features.

Sked is wirtten in PHP but it can execute console commands, shell scripts and even PHP callables.

## Installation

To install the package, run the following command:

```bash
composer require lavary\sked
```

## Starting the Scheduler

After the package is installed, command `sked` is symlinked to the `vendor/bin` directory. You may create a symlink of the file in `/usr/bin` directory, to have access to it from anywhere.

This is the only cron you need to install at server level, which runs every minute and delegates responsibility to the scheduler service (however you can change the frequency if you know what you're doing).

So the server-level cron job could be as following:

```bash
* * * * * path/to/php path/to/your/project/vendor/bin/sked schedule:run  >> /dev/null 2>&1
``` 

## Usage

All tasks should be defined in files with a name ending with `Tasks.php`, as an example: `adminstrativeTasks.php`. To run the tasks, you need to make sure Sked is aware of the task's location. By default Sked assume all the tasks reside in `Tasks` directory, in your project's root directory.

But if you need to have your tasks in another localtion, you need to create a YAML file named `sked.yml` in your project's root directory and put your tasks's location in place - in front of `src` key:

**sked.yml**
```
src: '/absolute/path/to/your/tasks/directory'
```

Please note that you need to modify the above path based on your project structure.

If your YAML file name is different, you can pass the name as an option to the `sked` command - when you're installing the cron:

```
* * * * * path/to/php path/to/your/project/vendor/bin/sked  --configuration-file="/path/to/custom/yaml/file"  >> /dev/null 2>&1
``` 

The scheduler scans the respective directory recursively, collects all the task files ending with `Tasks.php` and registers the tasks inside each file. You can define tasks in the same file or across different files and directories based on their usage.

Here's a basic task:

```php
<?php

// /var/www/project/Tasks/adminstrativeTasks.php

use Sked\Schedule;

$schedule = new Schedule();

$schedule->run('cp project project-bk')       
         ->everyMinute()
         ->description('Copying the project directory')
         ->appendOutputTo('/Users/lavary/www/sammi.log');

// ...

// You should return the schedule object

return $schedule; 
  
       
```

> **Important:** Please note that you need to return the `Schedule` instance at the end of each task file.


All tasks should be defined in files with a name ending with `Tasks.php` in your project, for instance: `adminstrativeTasks.php`. 

To run the tasks, you need to make sure Sked is aware of the task's location. By default Sked assume all the tasks reside in `Tasks` directory, in your project's root directory.

The scheduler scans the respective directory recursively, collects all the task files ending with `Tasks.php`, and registers the tasks inside each file. You can define tasks in the same file or across different files and directories based on their usage.

If you need to keep your task files in another location other than the default one, you may define the source path using the `--source` option - when installing the master cron:

```bash
* * * * * path/to/php path/to/your/project/vendor/bin/sked schedule:run --source=/path/to/the/Tasks/directory  >> /dev/null 2>&1
```

Here's another example:

```php
<?php

// ...

$schedule->run('./deploy.sh')
         ->in('/home')
         ->weekly()
         ->sundays()
         ->at('12:30')
         ->appendOutputTo('/var/log/backup.log');
         
// ...

// You should return the Schedule object.

return $scheduler;
```

## Generating Task Files Using the Task Generator

You can use the command line utility shipped with Sked to generate a task file and save some time. You may then edit the file based on your requirements.

To create task file run the following command in your terminal:

```bash
path/to/your/project/vendor/bin/sked make:task TaskFileName --frequency=everyFiveMinutes --constraint=weekdays
```

As a result, a file named `TaskFileNameTasks.php` will be generated in your `Tasks` directory.

You can also soecify the output destination path using the `output` option:

```bash
path/to/your/project/vendor/bin/sked make:task TaskFileName --frequency=everyFiveMinutes --constraint=weekdays --output="path/to/Tasks/directory"
```


You may use the `--help` option to see the list of available arguments and options along with their default values:

```bash
path/to/your/project/vendor/bin/sked --help
```

## Scheduling Frequency and Constraints

You can use a wide variety of scheduling frequencies according to your use case:

```php
| Method               | Description                            |
|----------------------|----------------------------------------|
| cron('* * * * * *')  | Run the task on a custom Cron schedule |
| everyMinute()        | Run the task every minute              |
| everyFiveMinutes()   | Run the task every five minutes        |
| everyTenMinutes()    | Run the task every ten minutes         |
| everyThirtyMinutes() | Run the task every thirthy minutes     |
| hourly()             | Run the task every hour                |
| daily()              | Run the task every day at midnight     |
| dailyAt('13:00')     | Run the task every day at 13:00        |
| twiceDaily(1, 13)    | Run the task daily at 1:00 & 13:00     |
| weekly()             | Run the task every week                |
| monthly()            | Run the task every month               |
| quarterly()          | Run the task every quarter             |
| yearly()             | Run the task every year                |
```

These methods may be combined with additional constraints to create even more finely tuned schedules that only run on certain days of the week. For example, to schedule a command to run weekly on Monday:

```php
<?php

// ...

$schedule->run('./backup.sh')
  ->weekly()
  ->mondays()
  ->at('13:00');

// ...

return $schedule;

```

Here's the list of constraints you can use with the above frequency methods:

```php
| Constraint    | Description                          |
|---------------|--------------------------------------|
| weekdays()    | Limit the task to weekdays           |
| sundays()     | Limit the task to Sunday             |
| mondays()     | Limit the task to Monday             |
| tuesdays()    | Limit the task to Tuesday            |
| wednesdays()  | Limit the task to Wednesday          |
| thirsdays()   | Limit the task to Thursday           |
| fridays()     | Limit the task to Friday             |
| saturdays()   | Limit the task to Saturday           |
| when(Closure) | Limit the task based on a truth test |
```

## Schedule a Task to Run Only Once 

You can schedule a task ro run once on certain date using `on()` method:

```php
<?php
// ...
$schedule->run('./backup.sh')
         ->on('2016-02-21');
// ...
```

You can also add the time using `at()` method:

```php
<?php
// ...
$schedule->run('./backup.sh')
         ->on('2016-02-21')
         ->at('03:45');
// ...
```

## Wake Up and Sleep Time 

You can also set an active duration for your task, so regardless of the frequency they will be turned off and on at certain times in the day or a period of time.

```php
<?php
$schedule->run('./backup.sh')
         ->everyFiveMinutes()
         ->wakeUpAt('2016-02-25 12:35')
         ->sleepAt('2016-02-26 12:35');

```

The above task will be run every five minutes from `2016-02-25 12:35` until `2016-02-26 12:35`.

You can also use the `lifetime()` method to do the same thing:

```php
<?php
$schedule->run('./backup.sh')
         ->everyFiveMinutes()
         ->lifetime('2016-02-25 12:35', '2016-02-26 12:35');

```

## Schedule Under Certain Conditions

You can run or skip a schedule based on a certain condition.

```php
<?php

// ...

$schedule->run('./backup.sh')->daily()->when(function () {
    return true;
});

// ...

return $schedule;

```

or skip it:

```php
<?php

// ...

$schedule->run('./backup.sh')->daily()->skip(function () {
    return false;
});

// ...

return $schedule;

```

## Prevent Task Overlaps

By default, scheduled tasks will be run even if the previous instance of the task is still running. To prevent this, you may use `withoutOverlapping()` method:

```php
<?php

// ...

$schedule->run('./backup.sh')->withoutOverlapping();

// ...

return $schedule;

```

## Handling Output

You can save the task output to a file:

```php
<?php

// ...

$shcedule->run('./back.sh')
         ->sendOutputTo('/var/log/backups.log');

// ...

return $schedule;

```

or append it:

```php
<?php

// ...

$shcedule->run('./back.sh')
         ->appendOutputTo('/var/log/backups.log');

// ...

return $schedule;

```

## Changing Directories

You can use the `in()` method to change directory before running a command:

```php
<?php

// ...

$schedule->run('./deploy.sh')
         ->in('/home')
         ->weekly()
         ->sundays()
         ->at('12:30')
         ->appendOutputTo('/var/log/backup.log');

// ...

return $schedule;

```

## Hooks

You can call a set of callbacks before and after  the command is run:

```php
<?php

// ...

$shcedule->run('./back.sh')
         ->before(function() {
            // Initialization phase
         })
         ->after(function() {
            // Cleanup phase
         });

// ...

return $schedule;

```

## Credits

Credit goes to [Taylor Otwell](https://github.com/taylorotwell) for creating such a nice tool and documentation.

## If You Need Help

Please submit all issues and questions using GitHub issues and I will try to help you.


## License
Sked is free software distributed under the terms of the MIT license.
