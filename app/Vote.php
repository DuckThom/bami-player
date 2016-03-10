<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model {

    protected $votes = 'votes';

    protected $fillable = ['ip', 'vote'];

    public function scopeValidVotes($query)
    {
        return $query->where('updated_at', '>', \DB::raw('DATE_SUB(NOW(), INTERVAL 10 SECOND)'));
    }

}