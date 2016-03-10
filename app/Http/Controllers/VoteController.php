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
            // Clear the votes table to start the new vote session
            Vote::truncate();

            Playing::first()->update([
                'voteskip' => 1
            ]);

            Vote::create([
                'ip'    => $request->ip(),
                'vote'  => 'yes'
            ]);

            return response()->json([
                'status'    => 'success',
                'message'   => 'Voting started'
            ]);
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
        Vote::where('ip', $request->ip())->update([
            'vote'  => 'yes'
        ]);

        return response()->json([
            'status'    => 'success',
            'message'   => 'Vote recorded'
        ]);
    }

}