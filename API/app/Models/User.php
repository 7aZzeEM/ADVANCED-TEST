<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'name',
        'username',
        'password',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
