<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

use DB;

class Video extends Model {

    protected $guarded = ['id'];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Add a video to the list of upcoming videos
     *
     * @param $name
     * @param $video_id
     * @return mixed
     */
    public static function setUpcoming($name, $video_id)
    {
        return DB::table('upcoming')->insert([
            'video_id'  => $video_id,
            'name'      => $name,
            'created_at'=> Carbon::now()
        ]);
    }

    /**
     * Remove a video from upcoming, add it to the history
     *
     * @param $video_id
     * @return mixed
     */
    public static function archive($video_id)
    {
        $video = DB::table('upcoming')->where('video_id', $video_id)->first();

        DB::table('history')->insert([
            'video_id'  => $video->video_id,
            'name'      => $video->name,
            'created_at'=> Carbon::now()
        ]);

        DB::table('upcoming')->delete($video->id);
    }

    /**
     * Check if the video is in the upcoming playlist
     *
     * @param $video_id
     * @return bool
     */
    public static function isUpcoming($video_id)
    {
        return DB::table('upcoming')->where('video_id', $video_id)->count() > 0;
    }

    /**
     * Get the list of upcoming videos
     *
     * @return mixed
     */
    public static function getUpcoming()
    {
        return DB::table('upcoming')->orderBy('created_at', 'asc')->get();
    }

    /**
     * Get the list of played videos (limit to 10 videos)
     *
     * @return mixed
     */
    public static function getHistory()
    {
        return DB::table('history')->orderBy('created_at', 'desc')->take(10)->get();
    }

    /**
     * Remove the video from the playlist
     *
     * @param $video_id
     */
    public static function removeFromPlaylist($video_id)
    {
        DB::table('upcoming')->where('video_id', $video_id)->delete();
    }

    /**
     * Check if a video is playing
     *
     * @return bool
     */
    public static function isPlaying()
    {
        if (DB::table('now_playing')->count())
            if (Carbon::now()->diffInSeconds(Carbon::parse(DB::table('now_playing')->first()->updated_at)) < 10)
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
            return self::isPlaying() ? DB::table('now_playing')->first() : false;
        else {
            if (DB::table('now_playing')->count()) {
                $now_playing = DB::table('now_playing')->where('id', 1)->update([
                    'name'       => $name,
                    'updated_at' => Carbon::now()
                ]);
            } else {
                $now_playing = DB::table('now_playing')->insert([
                    'name'       => $name,
                    'updated_at' => Carbon::now()
                ]);
            }

            return $now_playing;
        }
    }

    /**
     * Update the updated_at column of the now playing song
     * to indicate that it's still playing
     */
    public static function stillPlaying()
    {
        DB::table('now_playing')->where('id', 1)->update([
            'updated_at' => Carbon::now()
        ]);
    }

    /**
     * Remove the song from the currently playing list
     */
    public static function stopPlaying()
    {
        DB::table('now_playing')->where('id', 1)->update([
            'name'       => ''
        ]);
    }

}