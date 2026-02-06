<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // attributes hidden for serialization
    protected $hidden = ['password', 'token'];
}
