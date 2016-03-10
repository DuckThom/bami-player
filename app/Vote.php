<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model {

    protected $votes = 'votes';

    protected $fillable = ['ip', 'vote', 'fingerprint'];

    public function scopeValidVotes($query)
    {
        if (env('DB_CONNECTION') === 'sqlite')
            return $query->where('updated_at', '>', \DB::raw('datetime("now", "-10 seconds")'));
        else
            return $query->where('updated_at', '>', \DB::raw('DATE_SUB(NOW(), INTERVAL 10 SECOND)'));
    }

}