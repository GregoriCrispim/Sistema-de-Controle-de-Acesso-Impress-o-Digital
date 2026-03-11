<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('vouchers:reset')->dailyAt('00:00');
Schedule::command('db:backup')->dailyAt('02:00');
