<?php

namespace App\Http\Controllers;

use App\Models\Apt;
use Illuminate\Http\Request;

class APTController extends Controller
{
    //

    public function index()
    {

    }
    public function termsCondition()
    {
        $terms_condition = Apt::where('title','Terms and Conditions')->first();
        return response()->json(['data'=> $terms_condition],200);
    }

    public function privacyPolicy()
    {
        $terms_condition = Apt::where('title','Privacy and Policies')->first();
        return response()->json(['data' => $terms_condition],200);
    }
    public function store(Request $request)
    {
        $apt = new APT;
        $apt->title = $request->title;
        $apt->description = $request->description;
        $apt->save();
        return response()->json(['message' => 'Description Added Successfully'], 200);
    }

    public function updateApt(Request $request)
    {
        $apt = Apt::find($request->id);
        $apt->description = $request->description ?? $apt->description;
        $apt->save();
        return response()->json(['message' => 'Description Updated Successfully'], 200);
    }

}
