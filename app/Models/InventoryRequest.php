<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRequest extends Model
{
    use HasFactory;

    public $table = 'inventory_requests'; 

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'driver_id',
        'items',
        'status',
        'approved_by',
        'rejected_by',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'trip_id',
        'remarks', 
    ];

    protected $casts = [
        'id' => 'integer',
        'driver_id' => 'integer',
        'items' => 'array', // Cast items to array
        'status' => 'string',
        'rejection_reason' => 'string',
        'remarks' => 'string',
        'approved_by' => 'integer',
        'rejected_by' => 'integer',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime'
    ];

    public static $rules = [
        'driver_id' => 'required|exists:drivers,id',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'rejection_reason' => 'nullable|string|required_if:status,rejected',
        'remarks' => 'nullable|string|max:500'
    ];

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by', 'id');
    }

    public function rejector()
    {
        return $this->belongsTo(\App\Models\User::class, 'rejected_by', 'id');
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Check if request can be updated
     */
    public function canBeUpdated()
    {
        return in_array($this->status, [self::STATUS_PENDING]);
    }

    /**
     * Check if request can be approved
     */
    public function canBeApproved()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if request can be rejected
     */
    public function canBeRejected()
    {
        return $this->status === self::STATUS_PENDING;
    }
    
    /**
     * Get total quantity of all items
     */
    public function getTotalQuantityAttribute()
    {
        if (!$this->items) return 0;
        return collect($this->items)->sum('quantity');
    }
    
    /**
     * Get item count
     */
    public function getItemCountAttribute()
    {
        if (!$this->items) return 0;
        return count($this->items);
    }

    /**
     * Get product names with quantities
     */
    public function getProductSummaryAttribute()
    {
        if (!$this->items) return '';
        
        $productNames = [];
        foreach ($this->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $productNames[] = $product->name . ' (x' . $item['quantity'] . ')';
            }
        }
        return implode(', ', $productNames);
    }

    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }
    
}