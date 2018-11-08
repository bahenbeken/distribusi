<?php

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $fillable = ['username','password','email'];
    protected $table = 'users';
}