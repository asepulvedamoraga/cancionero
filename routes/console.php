<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('repertoire:cleanup-exports')->dailyAt('03:00')->withoutOverlapping();
