<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Housekeeping extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'housekeeping';

    protected $fillable = [
        'room_id',
        'assigned_to',
        'task_type',
        'priority',
        'status',
        'notes',
        'scheduled_date',
        'completed_at',
        'completed_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_at'   => 'datetime',
    ];

    const TASK_TYPES = ['cleaning', 'maintenance', 'inspection', 'turndown', 'deep_clean'];
    const PRIORITIES = ['low', 'normal', 'high', 'urgent'];
    const STATUSES   = ['pending', 'in_progress', 'completed', 'verified'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
