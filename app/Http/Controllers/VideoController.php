<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Video;

class VideoController extends Controller
{
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'video_id' => 'required',
            'name' => 'required'
        ]);

        if ($validator->passes()) {
            Video::setUpcoming($request->get('name'), $request->get('video_id'));

            return response()->json([
                'status' => 'success',
                'message' => 'Video has been added to the playlist'
            ]);
        } else
            return response()->json([
                'status' => 'failed',
                'message' => $validator->errors()
            ], 400);
    }
}
