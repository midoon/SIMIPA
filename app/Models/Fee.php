<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fee extends Model
{
    protected $guarded = ['id'];

    public function student(): BelongsTo{
        return $this->belongsTo(Student::class);
    }

    public function paymentType(): BelongsTo{
        return $this->belongsTo(PaymentType::class);
    }
}
