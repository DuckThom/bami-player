<?php namespace App;

use Illuminate\Database\Eloquent\Model;

use DB;

class Video extends Model {

    protected $guarded = ['id'];

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
            'name'      => $name
        ]);
    }

    /**
     * Remove a video from upcoming, add it to the history
     *
     * @param $video_id
     * @return mixed
     */
    public static function setHistory($video_id)
    {
        $video = DB::table('upcoming')->where('video_id', $video_id);

        $history = DB::table('history')->insert([
            'video_id'  => $video->video_id,
            'name'      => $video->name
        ]);

        $video->destroy();

        return $history;
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

}