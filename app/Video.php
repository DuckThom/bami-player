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

}