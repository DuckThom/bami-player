<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Upcoming extends Model {

    protected $table = 'upcoming';

    protected $guarded = ['id'];

    /**
     * Get a video by its video id
     *
     * @param $video_id
     * @return mixed
     */
    public static function getVideo($video_id)
    {
        return self::where('video_id', $video_id);
    }

    public static function deleteVideo($video_id)
    {
        self::where('video_id', $video_id)->delete();
    }

}