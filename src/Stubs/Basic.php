<?php

/*
|--------------------------------------------------------------------------------------
| Sked Task File
|--------------------------------------------------------------------------------------
|
| This file basically registers a new task to be executed by Sked
| To get the list of all frequency and constraint method, you may
| go to this link: https://github.com/lavary/sked#scheduling-frequency-and-constraints
|
*/

use Sked\Schedule;


$scheduler = new Schedule();

$scheduler->run('DummyCommand')
          ->description('DummyDescription')
          ->in('DummyPath')
          ->DummyFrequency()
          ->DummyConstraint()
          ->withoutOverlapping('00:15');


return $scheduler;          