<?php

namespace App\Console\Commands;

use App\Models\Survey;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ArchiveExpiredSurveys extends Command
{

    protected $signature = 'app:archive-expired-surveys';
    protected $description = 'Command description';

    public function handle()
    {
        $currentDate = Carbon::now();

        // Find surveys where end_date is less than current date and status is 'live'
        $surveys = Survey::where('end_date', '<', $currentDate)
            ->where('archive_status','false')
            ->get();

        foreach ($surveys as $survey) {
            $survey->archive_status = 'true';
            $survey->save();
            $this->info("Survey ID {$survey->id} has been archived.");
        }
        $this->info('All expired surveys have been archived.');
    }
}
