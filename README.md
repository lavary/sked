# Sked

Create a cron job once and for all, manage the rest right from the code.

[![Latest Unstable Version](https://poser.pugx.org/lavary/sked/v/unstable.svg)](https://packagist.org/packages/lavary/sked)

Sked is a framework-agnostic package for creating cron jobs using a fluent API. It's been built on top of the powerful [Laravel task scheduler](https://laravel.com/docs/master/scheduling), but the effort has been made to make Laravel Task Scheduler available to other environments and contexts, while providing additional features.

## Installation

To install the package, run the following command:

```bash
composer require lavary\sked
```

## Starting the Scheduler

After the package is installed, command `sked` is symlinked to the `vendor/bin` directory. You may make a symlink of the file in `/usr/bin` directory, to have access to it from anywhere.

This is the only cron you need to **add** at server level, which is run every minute and delegates responsibility to the scheduler service (however you can change the frequency if you know what you're doing).

So the server-level cron job could be as following:

```
* * * * * path/to/php path/to/your/project/vendor/bin/sked  >> /dev/null 2>&1
``` 

## Usage

All tasks should be defined in files. You can define tasks in the same file or across different files. Just remember to return the `Scheduler` object in each file:



```php
<?php

// /var/www/project/Tasks/adminstratives.php

use Sked\Schedule;

$schedule = new Schedule();

$schedule->task('cp project project-bk')       
         ->description('Copying the project directory')
         ->everyMinute()
         ->appendOutputTo('/Users/lavary/www/sammi.log');

// ...

// You should return the schedule object
return $schedule; 
  
       
```

Or:

```php
<?php

// ...

$schedule->task('./deploy.sh')
         ->cd('/home')
         ->weekly()
         ->sundays()
         ->at('12:30')
         ->appendOutputTo('/var/log/backup.log');
         
// ...

// You should return the Schedule object.
return $scheduler;
```

To run the tasks, we need to make sure Sked is aware of the task's location. To do this, you need to create a file named `sked.yml` in your project's root directory and put your tasks's location in place, in front of `src` key.

**sked.yml**
```
src: '/absolute/path/to/your/tasks/directory'
```

Please note that you need to modify the above path based on your project structure.

The scheduler scans the respective directory recursively, collects all the task files and registers the tasks inside them. As mentioned earlier, you can categorize the tasks in separate files and sub-directories based on their usage.

> By default Sked assume that all the tasks reside in `Tasks` directory, in your project's root directory.

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

$schedule->task(function () {
    // Runs once a week on Monday at 13:00...
})->weekly()
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


## Schedule Under Certain Conditions

You can run or skip a schedule based on a certain condition.

```php
<?php

// ...

$schedule->task('./backup.sh')->daily()->when(function () {
    return true;
});

// ...

return $schedule;

```

or skip it:

```php
<?php

// ...

$schedule->task('./backup.sh')->daily()->skip(function () {
    return false;
});

// ...

return $schedule;

```

## Prevent Task Overlaps

By default, scheduled tasks will be run even if the previous instance of the task is still running. To prevent this, you may use the withoutOverlapping method:

```php
<?php

// ...

$schedule->command('./backup.sh')->withoutOverlapping();

// ...

return $schedule;

```

## Handling Output

You save the task output to a file:

```php
<?php

// ...

$shcedule->task('./back.sh')
         ->sendOutputTo('/var/log/backups.log');

// ...

return $schedule;

```

or append it

```php
<?php

// ...

$shcedule->task('./back.sh')
         ->appendOutputTo('/var/log/backups.log');

// ...

return $schedule;

```


or Email it:

```
<?php

// ...

$shcedule->task('./back.sh')
         ->sendOutputTo('/var/log/backups.log')
         ->emailOutputTo('admin@example.com');
// ...

return $schedule;

```

## Changing Directories

You can use the `cd()` method to change directory before running a command:

```php
<?php

// ...

$schedule->task('./deploy.sh')
         ->cd('/home')
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

$shcedule->task('./back.sh')
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
