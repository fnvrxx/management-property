<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('invoices:generate')->dailyAt('01:00');
// Atau:
// Schedule::command('invoices:generate')->monthlyOn(1, '01:00');