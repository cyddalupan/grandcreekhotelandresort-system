<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

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
        $amenities = $this->amenities;
        if (!$amenities) {
            return '';
        }
        $list = is_array($amenities) ? $amenities : json_decode($amenities, true);
        return is_array($list) ? implode(', ', $list) : '';
    }
}
