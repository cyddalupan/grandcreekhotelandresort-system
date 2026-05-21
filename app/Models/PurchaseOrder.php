<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'supplier_id',
        'items',
        'total_amount',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'received_at',
    ];

    protected $casts = [
        'items'       => 'array',
        'total_amount'=> 'decimal:2',
        'approved_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Generate next PO number
    public static function generatePoNumber(): string
    {
        $last = self::whereDate('created_at', today())->orderBy('id', 'desc')->first();
        $seq = $last ? intval(substr($last->po_number, -3)) + 1 : 1;
        return 'PO-' . now()->format('ymd') . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    // Status helpers
    public function canApprove(): bool  { return $this->status === 'draft'; }
    public function canSend(): bool     { return $this->status === 'approved'; }
    public function canReceive(): bool  { return in_array($this->status, ['sent', 'partially_received']); }
    public function canCancel(): bool   { return !in_array($this->status, ['received', 'cancelled']); }
}
