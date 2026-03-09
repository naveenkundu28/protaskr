<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTeam extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsToMany(Client::class);
    }

    public function user()
    {
        return $this->belongsToMany(User::class, 'client_teams', 'user_id', 'client_id');
    }
}
