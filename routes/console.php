<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
  $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Schedule a daily command to update ticketing status to published.
 * 
 * This scheduled task runs the 'ticketing:update-status-publish' artisan command
 * every day at midnight (00:00) to automatically update ticketing statuses
 * that meet the criteria for being published.
 */
Schedule::command('ticketing:update-status-publish')->daily();
