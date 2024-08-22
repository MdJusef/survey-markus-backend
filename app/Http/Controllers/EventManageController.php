<?php

namespace App\Http\Controllers;

use App\Models\ManageBarcode;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EventManageController extends Controller
{
    // Generate QR Code for a survey

    public function generateQRCode($id)
    {

        // Find the survey by ID
        $survey = Survey::with('questions')->findOrFail($id);

        $exist_barcode = ManageBarcode::where('survey_id',$survey->id)->first();
        if ($exist_barcode) {
            return response()->json(['message'=>'Barcode already exist'],208);
        }

        // Generate a unique code
        $uniqueCode = Str::uuid()->toString();

        $surveyBarcode = new ManageBarcode;
        $surveyBarcode->survey_id = $survey->id;
        $surveyBarcode->barcode = $uniqueCode;
        $surveyBarcode->save();

        return response()->json([
            'message' => 'Barcode generated successfully',
            'barcode' => $surveyBarcode,
        ]);
    }

    // Get survey questions
    public function getSurveyQuestions()
    {
        $survey = ManageBarcode::with('survey.questions')->paginate(10);

        return response()->json([
            'survey' => $survey,
        ]);
    }

    public function getSingleSurveyQuestions($barcode)
    {
        $survey = ManageBarcode::where('barcode',$barcode)->with('survey.questions')->first();

        return response()->json([
            'survey' => $survey,
        ]);
    }
}
