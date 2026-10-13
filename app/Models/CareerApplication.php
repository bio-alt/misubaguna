<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerApplication extends Model
{
    protected $fillable = [
        'position',
        'name',
        'email',
        'phone',
        'cover_letter',
        'cv_path',
        'cv_original_name',
        'ip_address',
        'emailed_at',
    ];

    protected function casts(): array
    {
        return [
            'emailed_at' => 'datetime',
        ];
    }
}
