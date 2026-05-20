<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'capacity',
        'price_per_night',
        'amenities',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'amenities' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function availableRooms()
    {
        return $this->rooms()->where('status', 'available');
    }

    public function getAmenitiesListAttribute()
    {
        if (!$this->amenities) return [];
        return is_array($this->amenities) ? $this->amenities : json_decode($this->amenities, true);
    }
}
