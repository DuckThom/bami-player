<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\Blacklist;
use App\Video;

class VideoController extends Controller
{
    public function update()
    {
        return response()->json([
            'status'    => 'success',
            'payload'   => [
                    'upcoming'  => Video::getUpcoming(),
                    'history'   => Video::getHistory()
                ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'video_id'  => 'required',
            'name'      => 'required'
        ]);

        if ($validator->passes()) {
            if (Blacklist::checkName($request->get('name')))
            {
                if (!Video::isUpcoming($request->get('video_id')))
                {
                    Video::setUpcoming($request->get('name'), $request->get('video_id'));

                    return response()->json([
                        'status'    => 'success',
                        'message'   => 'Video has been added to the playlist'
                    ]);
                } else
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => 'Video is already in the playlist'
                    ], 208);
            } else
                return response()->json([
                    'status'    => 'failed',
                    'message'   => "The video name contains blacklisted words"
                ], 400);


        } else
            return response()->json([
                'status'    => 'failed',
                'message'   => $validator->errors()
            ], 400);
    }

    public function archive(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'video_id'  => 'required',
        ]);

        if ($validator->passes()) {
            if (Video::isUpcoming($request->get('video_id')))
            {
                Video::archive($request->get('video_id'));

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Video has been moved to the history'
                ]);
            } else
                return response()->json([
                    'status'    => 'failed',
                    'message'   => 'Video is not in the upcoming playlist'
                ], 404);

        } else
            return response()->json([
                'status'    => 'failed',
                'message'   => $validator->errors()
            ], 400);
    }

    public function delete($video_id)
    {
        if ($video_id !== null) {
            if (Video::isUpcoming($video_id))
            {
                Video::removeFromPlaylist($video_id);

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Video has been removed from the playlist'
                ]);
            } else
                return response()->json([
                    'status'    => 'failed',
                    'message'   => 'Video was not found in the upcoming playlist'
                ], 404);
        } else
            return response()->json([
                'status'    => 'failed',
                'message'   => 'A video id is required'
            ], 400);
    }
}
