<?php namespace App\Helpers;

use Carbon\Carbon;

use App\Upcoming;
use App\Playing;
use App\History;
use App\Vote;

class Video {

    /**
     * Remove a video from upcoming, add it to the history
     *
     * @param $video_id
     * @return mixed
     */
    public static function archive($video_id)
    {
        $video = Upcoming::getVideo($video_id)->first();

        History::create([
            'video_id'  => $video->video_id,
            'name'      => $video->name
        ]);

        $video->delete();
    }

    /**
     * Check if the video is in the upcoming playlist
     *
     * @param $video_id
     * @return bool
     */
    public static function isUpcoming($video_id)
    {
        return Upcoming::getVideo($video_id)->count() > 0;
    }

    /**
     * Get the list of upcoming videos
     *
     * @return mixed
     */
    public static function getUpcoming()
    {
        return Upcoming::orderBy('created_at', 'asc')->get();
    }

    /**
     * Get the list of played videos (limit to 10 videos)
     *
     * @return mixed
     */
    public static function getHistory()
    {
        return History::orderBy('created_at', 'desc')->take(10)->get();
    }

    /**
     * Remove the video from the playlist
     *
     * @param $video_id
     */
    public static function removeFromPlaylist($video_id)
    {
        Upcoming::deleteVideo($video_id);
    }

    /**
     * Check if a video is playing
     *
     * @return bool
     */
    public static function isPlaying()
    {
        if (Playing::count())
            if (Carbon::now()->diffInSeconds(Carbon::parse(Playing::first()->updated_at)) < 10 || \File::exists(storage_path('app/server-is-playing')))
                return true;
            else
                self::stopPlaying();

        return false;
    }

    /**
     * Get or set the song that's currently playing
     *
     * @param null $name
     * @return mixed|static|bool
     */
    public static function nowPlaying($name = null)
    {
        if ($name === null)
            return self::isPlaying() ? Playing::first() : false;
        else {
            if (Playing::count()) {
                $now_playing = Playing::first()->update([
                    'name'      => $name,
                    'voteskip'  => 0
                ]);
            } else {
                $now_playing = Playing::create([
                    'name'      => $name,
                    'voteskip'  => 0
                ]);
            }

            // Reset all the votes back to 'no'
            Vote::where('vote', 'yes')->update([
                'vote' => 'no'
            ]);

            return $now_playing;
        }
    }

    /**
     * Update the updated_at column of the now playing song
     * to indicate that it's still playing
     */
    public static function stillPlaying()
    {
        Playing::first()->update([
            'updated_at' => Carbon::now()
        ]);
    }

    /**
     * Remove the song from the currently playing list
     */
    public static function stopPlaying()
    {
        if (\File::exists(storage_path('app/server-is-playing')))
            \File::delete(storage_path('app/server-is-playing'));

        Playing::first()->update([
            'name'       => '',
            'voting'     => -1
        ]);
    }

    /**
     * Return the voting status of the currently playing song
     *
     * @return array
     */
    public static function votingStatus()
    {
        return [
            'status'    => Playing::first()->voteskip,
            'yes'       => Vote::where('vote', 'yes')->validVotes()->count(),
            'total'     => Vote::validVotes()->count()
        ];
    }

}