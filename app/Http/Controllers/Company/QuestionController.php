<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\AnonymousSurveyAnswer;
use App\Models\Answer;
use App\Models\Project;
use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{

    public function index(Request $request)
    {
        $company_id = auth()->user()->id;
        $query = Survey::where('user_id', $company_id)->with('project')
            ->where('end_date', '>=', now()->toDateString()) //new line added 3/2/25
            ->withCount('questions')->withCount('answers');
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('survey_name', 'like', '%' . $search . '%');
        }
        $per_page = $request->per_page ?? 10;
        $questions = $query->paginate($per_page);
        return response()->json($questions);
    }

    public function create() {}

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'user_id' => 'integer|exists:users,id',
            'project_id' => 'required|integer|exists:projects,id',
            'survey_id' => 'required|integer|exists:surveys,id',
            'questions' => 'required',
            'questions.*.question_en' => 'string',
            'questions.*.comment' => 'boolean'
        ]);

        $questions = $request->input('questions');
        Log::info('Questions array: ' . json_encode($questions));
        $questions = json_decode($questions, true);
        try {
            $responses = [];
            foreach ($questions as $q) {
                $question = new Question();
                $question->user_id = auth()->user()->id;
                $question->project_id = $request->project_id;
                $question->survey_id = $request->survey_id;
                $question->question_en = $q['question_en'];
                //$question->question_jer = $q['question_jer'];
                $question->comment = $q['comment'];
                $question->save();

                $responses[] = $question;
            }

            return response()->json([
                'message' => 'Questions added successfully',
                'data' => $responses
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding questions: ' . $e->getMessage());

            return response()->json([
                'status' => 500,
                'message' => 'An error occurred while adding the questions',
            ], 500);
        }
    }

    public function show(string $id)
    {
        $company_id = auth()->user()->id;
        $questions = Survey::where('user_id', $company_id)->where('id', $id)->with('project', 'questions', 'answers')->paginate(10);
        return response()->json($questions);
    }

    public function edit(string $id)
    {
        //
    }

    //    public function update(Request $request, string $id)
    //    {
    //        //
    //    }
    public function update(Request $request, string $id) {}


    public function destroy(string $id)
    {
        //
    }

    //    public function questionBasedReport(Request $request)
    //    {
    //
    //        $survey_id = $request->input('survey_id');
    //        $project_id = $request->input('project_id');
    //        $perPage = $request->input('per_page', 10); // Default to 10 if 'per_page' is not present
    //
    //
    //
    //        $options = [1, 2, 3, 4, 5];
    //
    //        $surveys = Survey::with(['questions.answer', 'project'])
    //            ->where('project_id', $project_id)
    //            ->where('id', $survey_id)
    //            ->first();
    //
    //        $report = [];
    //
    //        if ($surveys) {
    //            $questions = $surveys->questions()->paginate($perPage); // Pagination on questions
    //
    //            foreach ($questions as $question) {
    //                $anonymous_survey_count = AnonymousSurveyAnswer::where('survey_id',$survey_id)->where('question_id',$question->id)->count();
    //                $app_survey_count = Answer::where('survey_id',$survey_id)->where('question_id',$question->id)->count();
    //                $overall_survey_count = $anonymous_survey_count + $app_survey_count;
    //
    //                $appUsers = $question->answer->groupBy('user_id')->count();
    //                $qrCodeUser = AnonymousSurveyAnswer::where('survey_id',$survey_id)->where('question_id',$question->id)->groupBy('ip_address')->count();
    //                $totalUsers = $appUsers + $qrCodeUser;
    //                $appComments = $question->answer->where('comment', '!=', null)->count();
    //                $qrCodeComments = AnonymousSurveyAnswer::where('question_id',$question->id)->where('comment','!=',null)->count();
    //
    //                $optionCounts = $question->answer->groupBy('answer')->map->count();
    //
    //                //$qrOptionCounts = $question->anonymous_answer->groupBy('answer')->map->count();
    //                $totalComments = $appComments + $qrCodeComments;
    //
    //                $optionPercentages = collect($options)->mapWithKeys(function ($option) use ($optionCounts, $totalUsers) {
    //                    $count = $optionCounts->get($option, 0);
    //                    return [$option => ($totalUsers > 0) ? ($count / $totalUsers) * 100 : 0];
    //                });
    //
    //                $report[] = [
    //                    'project' => $surveys->project->project_name,
    //                    'survey' => $surveys->survey_name,
    //                    'question_id' => $question->id,
    //                    'question' => $question->question_en,
    //                    'total_comments' => $totalComments,
    //                    'total_users' => $totalUsers,
    //                    'option_percentages' => $optionPercentages,
    //                    'qr_code_survey' => $anonymous_survey_count,
    //                    'app_survey_count' => $app_survey_count,
    //                    'overall_survey' => $overall_survey_count,
    //                ];
    //            }
    //
    //            // Return paginated data
    //            return response()->json([
    //                'emoji_or_star' => $surveys->emoji_or_star,
    //                'data' => $report,
    //                'pagination' => [
    //                    'total' => $questions->total(),
    //                    'per_page' => $questions->perPage(),
    //                    'current_page' => $questions->currentPage(),
    //                    'last_page' => $questions->lastPage(),
    //                    'from' => $questions->firstItem(),
    //                    'to' => $questions->lastItem(),
    //                ]
    //            ]);
    //        }
    //
    ////        return response()->json([]);
    //    }
    public function questionBasedReport(Request $request)
    {
        $survey_id = $request->input('survey_id');
        $project_id = $request->input('project_id');
        $perPage = $request->input('per_page', 10); // Default to 10 if 'per_page' is not present

        $options = [1, 2, 3, 4, 5];

        $surveys = Survey::with(['questions.answer.user:id,email', 'project'])
            ->where('project_id', $project_id)
            ->where('id', $survey_id)
            ->first();
        // return $surveys;
        $report = [];

        if ($surveys) {
            $questions = $surveys->questions()->paginate($perPage); // Pagination on questions
            // return $questions;

            foreach ($questions as $question) {
                $anonymous_survey_count = AnonymousSurveyAnswer::where('survey_id', $survey_id)
                    ->where('question_id', $question->id)
                    ->count();
                $app_survey_count = Answer::where('survey_id', $survey_id)
                    ->where('question_id', $question->id)
                    ->count();
                $overall_survey_count = $anonymous_survey_count + $app_survey_count;
                $appUsers = $question->answer->groupBy('user_id')->count();
                $qrCodeUser = AnonymousSurveyAnswer::where('survey_id', $survey_id)
                    ->where('question_id', $question->id)
                    ->groupBy('ip_address')
                    ->count();
                $totalUsers = $appUsers + $qrCodeUser;

                $appComments = $question->answer->where('comment', '!=', null)->count();
                $qrCodeComments = AnonymousSurveyAnswer::where('question_id', $question->id)
                    ->where('comment', '!=', null)
                    ->count();
                $totalComments = $appComments + $qrCodeComments;

                // Calculate combined option counts
                $appOptionCounts = $question->answer->groupBy('answer')->map->count();
                $qrOptionCounts = AnonymousSurveyAnswer::where('survey_id', $survey_id)
                    ->where('question_id', $question->id)
                    ->get()
                    ->groupBy('answer')
                    ->map->count();

                $combinedOptionCounts = collect($options)->mapWithKeys(function ($option) use ($appOptionCounts, $qrOptionCounts) {
                    $appCount = $appOptionCounts->get($option, 0);
                    $qrCount = $qrOptionCounts->get($option, 0);

                    return [$option => $appCount + $qrCount];
                });


                // Calculate option percentages
                $optionPercentages = $combinedOptionCounts->map(function ($count) use ($overall_survey_count) {
                    return ($overall_survey_count > 0) ? ($count / $overall_survey_count) * 100 : 0;
                });

                $report[] = [
                    'project' => $surveys->project->project_name,
                    'survey' => $surveys->survey_name,
                    'question_id' => $question->id,
                    'question' => $question->question_en,
                    'total_comments' => $totalComments,
                    'total_users' => $totalUsers,
                    'option_percentages' => $optionPercentages,
                    'qr_code_survey' => $anonymous_survey_count,
                    'app_survey_count' => $app_survey_count,
                    'overall_survey' => $overall_survey_count,
                ];
            }
        }

        return response()->json([
            'emoji_or_star' => $surveys->emoji_or_star,
            'data' => $report,
            'pagination' => [
                'total' => $questions->total(),
                'per_page' => $questions->perPage(),
                'current_page' => $questions->currentPage(),
                'last_page' => $questions->lastPage(),
                'from' => $questions->firstItem(),
                'to' => $questions->lastItem(),
            ],
        ]);
    }

    public function exportSurvey(Request $request)
    {
        $survey_id = $request->input('survey_id');
        $project_id = $request->input('project_id');
        $is_anonymous = $request->input('is_anonymous');
        // return $is_anonymous == true;
        $survey = Survey::with(['questions.answer.user:id,email', 'questions.anonymous_answer', 'project'])
            ->where('project_id', $project_id)
            ->where('id', $survey_id)
            ->firstOrFail();
        // return $survey;
        $data = [];

        foreach ($survey->questions as $index => $question) {
            // Named Answers
            foreach ($question->answer as $ans) {
            $data[] = [
                'emoji_or_star' => $survey->emoji_or_star,
                'participant' => $is_anonymous == true ? 'Anonymous' : $ans->user->email,
                'user_id' => $ans->user_id,
                'project_id' => $survey->project_id,
                'project_name' => $survey->project->project_name,
                'survey_id' => $survey->id,
                'survey_name' => $survey->survey_name,
                'question_id' => $question->id,
                'qn' => $index + 1,
                'question' => $question->question_en,
                'answer_score' => $ans->answer,
                'emoji' => $this->getEmoji($ans->answer, $survey->emoji_or_star),
                'comment' => $ans->comment ?? '-',
                'via' => 'app',
                'date' => $ans->created_at->format('d.m.Y'),
                'time' => $ans->created_at->format('H:i A'),
            ];
            }

            // Anonymous Answers
            foreach ($question->anonymous_answer as $ans) {
            $data[] = [
                'sl_no' => $index + 1,
                'emoji_or_star' => $survey->emoji_or_star,
                'participant' => 'no name / qr-code',
                'user_id' => null,
                'project_id' => $survey->project_id,
                'project_name' => $survey->project->project_name,
                'survey_id' => $survey->id,
                'survey_name' => $survey->survey_name,
                'question_id' => $question->id,
                'qn' => $index + 1,
                'question' => $question->question_en,
                'answer_score' => $ans->answer,
                'emoji' => $this->getEmoji($ans->answer, $survey->emoji_or_star),
                'comment' => $ans->comment ?? '-',
                'via' => 'qr-code',
                'date' => $ans->created_at->format('d.m.Y'),
                'time' => $ans->created_at->format('H:i A'),
            ];
            }
        }

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    private function getEmoji($answer, $type)
{
    if ($type === 'emoji') {
        $emojiMap = [
            1 => '😡',
            2 => '😟',
            3 => '😐',
            4 => '🙂',
            5 => '😄',
        ];
        return $emojiMap[$answer] ?? '';
    }

    if ($type === 'star') {
        $colorMap = [
            1 => '#FF0000',
            2 => '#ff9100',
            3 => '#FFD500',
            4 => '#B5D900',
            5 => '#07CC00',
        ];

        $color = $colorMap[$answer] ?? 'gray';
        return "<span style='color: $color;'>" . str_repeat('★', (int) $answer) . "</span>";
    }

    return '';
}

    // public function questionBasedUser(Request $request)
    // {
    //     $question_id = $request->input('question_id');
    //     $query = Answer::where('question_id',$question_id)->with('user');
    //     if ($request->filled('search'))
    //     {
    //         $search = $request->input('search');
    //         $query->whereHas('user', function ($query) use ($search) {
    //             $query->where('name', 'like', '%'. $search . '%');
    //         });
    //     }
    //     $user = $query->paginate($per_page = $request->per_page ?? 10);

    //     return response()->json($user);
    // }

    // no change there

    public function questionBasedUser(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $question_id = $request->input('question_id');

        $query = Question::where('id', $question_id)
            ->with([
                'anonymous_answer' => function ($query) {
                    $query->select('id', 'question_id', 'answer', 'comment', 'created_at', 'updated_at')
                        ->whereNotNull('comment');
                },
                'answer' => function ($query) {
                    $query->select('id', 'survey_id', 'user_id', 'question_id', 'answer', 'comment', 'created_at', 'updated_at')
                        ->whereNotNull('comment')
                        ->with(['user' => function ($query) {
                            $query->select('id', 'name', 'email', 'company_id', 'image', 'address', 'phone_number', 'role_type', 'created_at', 'updated_at');
                        }]);
                }
            ])
            ->select('id')
            ->first();

        if (!$query) {
            return response()->json(['message' => 'Question not found'], 404);
        }


        $data = collect($query->anonymous_answer)
            ->merge($query->answer)
            ->transform(function ($item) {
                return [
                    'id'          => $item->id,
                    'survey_id'   => $item->survey_id ?? null,
                    'user_id'     => $item->user_id ?? null,
                    'question_id' => $item->question_id,
                    'answer'      => $item->answer,
                    'comment'     => $item->comment,
                    'created_at'  => $item->created_at,
                    'updated_at'  => $item->updated_at,
                    'user'        => $item->user ?? null
                ];
            })->values();
        // Pagination Apply
        $page = LengthAwarePaginator::resolveCurrentPage();
        $paginatedData = new LengthAwarePaginator(
            $data->forPage($page, $perPage),
            $data->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return response()->json($paginatedData);
    }


    public function updateQuestions(Request $request)
    {
        try {
            // Decode the JSON input
            $questions = json_decode($request->input('questions'), true);

            // Check for JSON errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'Invalid JSON format'], 400);
            }

            // Ensure the decoded JSON is an array
            if (!is_array($questions)) {
                return response()->json(['error' => 'Questions should be an array'], 400);
            }

            $responses = [];

            // Iterate over each question and update accordingly
            foreach ($questions as $question) {
                // Validate that the question ID exists
                if (!isset($question['id'])) {
                    return response()->json(['error' => 'Question ID is required'], 400);
                }

                // Find the question or return a 404 error
                $question_data = Question::find($question['id']);

                if (!$question_data) {
                    return response()->json(['error' => 'Question not found for ID ' . $question['id']], 404);
                }

                $already_response_or_not = Answer::where('survey_id', $question_data->survey_id)->first();
                if ($already_response_or_not) {
                    return response()->json(['message' => 'The user has already responded to this survey, so it is not editable'], 400);
                }

                // Update the question fields
                $question_data->question_en = $question['question_en'] ?? $question_data->question_en;
                $question_data->comment = $question['comment'] ?? $question_data->comment;

                // Save the updated question
                $question_data->save();

                // Add the updated question to the response array
                $responses[] = $question_data;
            }

            return response()->json([
                'message' => 'Questions updated successfully',
                'data' => $responses
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating questions: ' . $e->getMessage()], 500);
        }
    }
}
