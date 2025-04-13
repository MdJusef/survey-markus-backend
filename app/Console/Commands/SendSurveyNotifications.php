<?php

namespace App\Console\Commands;

use App\Models\Answer;
use App\Models\Survey;
use App\Models\User;
use App\Notifications\SurveyReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSurveyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-survey-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = now();

        $statuses = Answer::where('next_notification_at', '<=', $now)->get();

        foreach ($statuses as $status) {
            $user = User::find($status->user_id);
            $survey = Survey::find($status->survey_id);

            if ($survey && $survey->end_date && Carbon::parse($survey->end_date)->isPast()) {
                continue;
            }

            if (!$user || !$survey) continue;

            $data = [
                'survey_id' => $survey->id,
                'user_id' => $user->id,
                'message' => 'It\'s time again for the "' . $survey->survey_name . '" survey — share your feedback with us!',
            ];

            $user->notify(new SurveyReminderNotification($data));


            switch ($survey->repeat_status) {
                case 'daily': $next = $now->copy()->addDay(); break;
                case 'weekly': $next = $now->copy()->addWeek(); break;
                case 'monthly': $next = $now->copy()->addMonth(); break;
                default: $next = null;
            }

            if ($next) {
                $status->update(['next_notification_at' => $next]);
            }
        }

        Log::info('Survey notifications sent successfully.');

        return Command::SUCCESS;
    }
}
