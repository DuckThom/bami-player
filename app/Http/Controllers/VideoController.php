<?php

namespace App\Http\Controllers;

use App\Vote;
use App\Upcoming;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\Blacklist;
use App\Helpers\Video;

class VideoController extends Controller
{
    /**
     * Server polling handler
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        if ($request->has('playing') && Video::nowPlaying() !== false) {
            if (Video::nowPlaying()->name !== "") {
                $request->get('playing') === true ? Video::stillPlaying() : false;
            }
        }

        if (Vote::where('fingerprint', $request->get('fp'))->count())
            Vote::where('fingerprint', $request->get('fp'))->update([]);
        else
            Vote::create([
                'ip'            => $request->ip(),
                'fingerprint'   => $request->get('fp'),
                'vote'          => 'no'
            ]);

        return response()->json([
            'status'    => 'success',
            'payload'   => [
                    'upcoming'  => Video::getUpcoming(),
                    'history'   => Video::getHistory(),
                    'playing'   => Video::nowPlaying(),
                    'voting'    => Video::votingStatus()
                ]
        ]);
    }

    /**
     * Try to save a video to the upcoming playlist
     *
     * TODO: Prevent duplicate entries when spamming this function
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'video_id'  => 'required',
            'name'      => 'required'
        ]);

        if ($validator->passes()) {
            if (Blacklist::checkName($request->get('name')))
            {
                if (Upcoming::getVideo($request->get('video_id'))->count() === 0)
                {
                    Upcoming::firstOrCreate([
                        'video_id'  => $request->get('video_id'),
                        'name'      => $request->get('name')
                    ]);

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

    /**
     * Move a video to the history
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Remove a video from upcoming without moving it to history
     *
     * @param $video_id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Check if a video is playing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function is_playing()
    {
        return response()->json([
            'status'  => 'success',
            'playing' => Video::isPlaying()
        ]);
    }

    /**
     * Get or set the currently playing song
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function now_playing(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name'  => 'required',
        ]);

        if ($validator->passes()) {
            Video::nowPlaying($request->get('name'));

            return response()->json([
                'status'    => 'success',
                'message'   => 'Updated now playing song'
            ]);

        } else
            return response()->json([
                'status'    => 'failed',
                'message'   => $validator->errors()
            ], 400);
    }

    /**
     * Remove the song from now playing
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stop_playing()
    {
        if (Video::isPlaying())
        {
            Video::stopPlaying();

            return response()->json([
                'status'    => 'success',
                'message'   => 'Removed video from now playing'
            ]);
        } else
            return response()->json([
                'status'    => 'success',
                'message'   => 'No video is currently playing'
            ]);
    }
}
