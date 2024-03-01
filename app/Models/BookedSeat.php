<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $booked_seats
 */
class BookedSeat extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

}
