<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
     protected $guarded = ['id'];

     public function schedules(): HasMany{
        return $this->hasMany(Schedule::class);
     }
}
