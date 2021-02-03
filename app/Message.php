<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'orig', 'dest', 'text',  'updated_at', 'created_at', 'sent_at', 'draft', 'inbox', 'file', 'image', 'audio', 'secure'
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