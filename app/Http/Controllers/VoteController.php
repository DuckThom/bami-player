<?php namespace App\Http\Controllers;

use App\Playing;
use App\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller {

    /**
     * Start a voting session
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(Request $request)
    {
        $voteskip = Playing::first()->voteskip;

        if ($voteskip != 1)
        {
            if ($request->has('fp')) {
                // Clear the votes table to start the new vote session
                Vote::truncate();

                Playing::first()->update([
                    'voteskip' => 1
                ]);

                Vote::create([
                    'ip'            => $request->ip(),
                    'fingerprint'   => $request->get('fp'),
                    'vote'          => 'yes'
                ]);

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Voting started'
                ]);
            } else
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Request is missing a fingerprint'
                ], 400);
        } else
            return response()->json([
                'status'    => 'failure',
                'message'   => 'Voting disabled or already started'
            ], 400);
    }

    /**
     * Record a vote
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function skip(Request $request)
    {
        if ($request->has('fp')) {
            Vote::where('fingerprint', $request->get('fp'))->update([
                'vote' => 'yes'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Vote recorded'
            ]);
        } else
            return response()->json([
                'status' => 'failure',
                'message' => 'Request is missing a fingerprint'
            ], 400);
    }

}