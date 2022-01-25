<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Caller extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'station', 'starttime', 'stoptime', 'delay', 'enable'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'created_at'
    ];
}