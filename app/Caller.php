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
        'id', 'title', 'stations', 'starttime', 'stoptime',  'enable'
    ];
	
	protected $casts = [
		'stations' => 'array',
	];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'created_at'
    ];

    protected $table = 'caller';
}

