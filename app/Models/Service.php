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
        'month',
        'year',
        'hours',
        'service_status',
        'start_date',
        'end_date',
        'front_resource',
        'working_resource',
        'cancel_reason',
        'replacement_reason',
    ];


    public function frontResource()
    {
        return $this->belongsTo(User::class, 'front_resource')->withTrashed();
    }

    public function workingResource()
    {
        return $this->belongsTo(User::class, 'working_resource')->withTrashed();
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
