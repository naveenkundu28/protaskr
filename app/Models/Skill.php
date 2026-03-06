<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function primaryUsers()
    {
        return $this->belongsToMany(User::class, 'user_primary_skills')->withTimestamps();
    }

    public function secondaryUsers()
    {
        return $this->belongsToMany(User::class, 'user_secondary_skills')->withTimestamps();
    }
}
