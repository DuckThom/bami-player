<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Playing extends Model {

    protected $table = 'now_playing';

    protected $fillable = ['id', 'name', 'voteskip', 'created_at', 'updated_at'];

}