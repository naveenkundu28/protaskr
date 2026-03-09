<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_type',
        'client_id',
        'service_id',
        'hours',
        'service_status',
        'start_date',
        'end_date'
    ];


    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
