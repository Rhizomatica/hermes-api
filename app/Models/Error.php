<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Error extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'controller', 'error_code', 'error_message', 'stacktrace', 'user_id', 'station', 'updated_at', 'created_at'
    ];
}