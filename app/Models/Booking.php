<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'guest_name',
        'guest_email',
        'guest_phone',
        'room_id',
        'check_in',
        'check_out',
        'adults',
        'children',
        'status',
        'total_amount',
        'paid_amount',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_in'    => 'date',
            'check_out'   => 'date',
            'adults'      => 'integer',
            'children'    => 'integer',
            'total_amount'=> 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function roomType()
    {
        return $this->hasOneThrough(
            \App\Models\RoomType::class,
            Room::class,
            'id',
            'id',
            'room_id',
            'room_type_id'
        );
    }

    public function nights()
    {
        return max(0, $this->check_in->diffInDays($this->check_out));
    }

    public function balance()
    {
        return $this->total_amount - $this->paid_amount;
    }
}
