<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('donations:process-recurring')->dailyAt('09:00');
Schedule::command('currency:fetch-rates')->dailyAt('06:00');
