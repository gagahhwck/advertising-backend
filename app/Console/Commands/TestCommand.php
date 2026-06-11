<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class TestCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'test:command';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'test command';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return int
   */
  public function handle()
  {
    $this->info('[start] Test Command');

    // $existingSchedule = [
    //     'id'                => 22874,
    //     'meeting_name'      => 'EDU321 - Graduate Seminar on Educational Management and Leadership',
    //     'start_date'        => Carbon::parse('2025-09-25 13:00:00.000'),
    //     'end_date'          => Carbon::parse('2025-09-25 15:30:00.000'),
    //     'day_id'            => 4,
    // ];

    // $new_schedule = [
    //     'date_start' => '2025-09-25 09:00:00.000',
    //     'date_end'   => '2026-01-15 14:30:00.000',
    //     'time_start' => '09:00',
    //     'time_end'   => '14:30',
    // ];

    // $date_start = Carbon::parse($new_schedule['date_start']);
    // $date_end = Carbon::parse($new_schedule['date_end']);
    // while ($date_start <= $date_end) {
    //     $date = $date_start->toDateString();
    //     $time_start = Carbon::parse($date . ' ' . $new_schedule['time_start'] . ':00.000');
    //     $time_end = Carbon::parse($date . ' ' . $new_schedule['time_end'] . ':00.000');
    //     if (($existingSchedule['start_date']->toDateString() == $date && $existingSchedule['start_date']->between($time_start, $time_end)) ||
    //         ($existingSchedule['end_date']->toDateString() == $date && $existingSchedule['end_date']->between($time_start, $time_end)) ||
    //         ($existingSchedule['start_date']->lessThanOrEqualTo($time_start) && $existingSchedule['end_date']->greaterThanOrEqualTo($time_end))
    //     ) {
    //         $this->error('Conflict with existing schedule ID ' . $existingSchedule['id'] . ' (' . $existingSchedule['meeting_name'] . ') on ' . $date . ' from ' . $existingSchedule['start_date']->format('H:i') . ' to ' . $existingSchedule['end_date']->format('H:i'));
    //         $this->info('[end] Test Command');
    //         return 0;
    //     }
    //     $date_start->addDay();
    // }
    // $this->info('no conflict detected');
    $this->info('[end] Test Command');
  }
}
