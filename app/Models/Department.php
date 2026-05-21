<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'description', 'manager', 'active', 'item_count',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'department_id');
    }

    public function movementsFrom(): HasMany
    {
        return $this->hasMany(Movement::class, 'from_department');
    }

    public function movementsTo(): HasMany
    {
        return $this->hasMany(Movement::class, 'to_department');
    }
}
