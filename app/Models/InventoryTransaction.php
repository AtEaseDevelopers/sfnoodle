<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class InventoryTransaction extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'inventory_transactions';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Transaction type constants
    public const TYPE_STOCK_IN = 1;
    public const TYPE_STOCK_OUT = 2;
    public const TYPE_STOCK_RETURN = 3;
    public const TYPE_STOCK_COUNT = 4;

    public $fillable = [
        'driver_id',
        'product_id',
        'quantity',
        'type',
        'remark',
        'invoice_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'driver_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'type' => 'integer',
        'remark' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'driver_id' => 'required|exists:drivers,id',
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'type' => 'required|integer|in:1,2,3',
        'remark' => 'nullable|string|max:255',
    ];

    /**
     * Boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set current date if not provided
            if (!isset($model->date)) {
                $model->date = now();
            }
            
            // Format date properly for database
            if ($model->date instanceof Carbon) {
                $model->date = $model->date->format('Y-m-d H:i:s');
            }
        });
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id', 'id');
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class, 'invoice_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }

    /**
     * Set date attribute properly for database
     */
    public function setDateAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['date'] = Carbon::createFromFormat('d-m-Y H:i:s', $value)->format('Y-m-d H:i:s');
        } elseif ($value instanceof Carbon) {
            $this->attributes['date'] = $value->format('Y-m-d H:i:s');
        } else {
            $this->attributes['date'] = $value;
        }
    }

    /**
     * Get type as text
     */
    public function getTypeTextAttribute()
    {
        $types = [
            self::TYPE_STOCK_IN => 'Stock In',
            self::TYPE_STOCK_OUT => 'Stock Out',
            self::TYPE_STOCK_RETURN => 'Stock Return'
        ];
        
        return $types[$this->type] ?? 'Unknown';
    }

    /**
     * Get type options for dropdown
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_STOCK_IN => 'Stock In',
            self::TYPE_STOCK_OUT => 'Stock Out',
            self::TYPE_STOCK_RETURN => 'Stock Return'
        ];
    }

    /**
     * Get sign for quantity (positive for in, negative for out/return)
     */
    public function getQuantitySignAttribute()
    {
        return $this->type == self::TYPE_STOCK_IN ? '+' : '-';
    }

    /**
     * Get formatted quantity with sign
     */
    public function getFormattedQuantityAttribute()
    {
        return $this->quantity_sign . abs($this->quantity);
    }


    public static function createTransaction(
        int $driverId,
        int $productId,
        int $quantity,
        int $type,
        string $remark = null,
        int $invoiceId = null
    ): InventoryTransaction {
        // Validate transaction type
        if (!in_array($type, [self::TYPE_STOCK_IN, self::TYPE_STOCK_OUT, self::TYPE_STOCK_RETURN])) {
            throw new \Exception('Invalid transaction type. Must be 1 (Stock In), 2 (Stock Out), or 3 (Stock Return).');
        }

        // Validate quantity based on type
        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than 0.');
        }

        // Get driver name
        $driver = \App\Models\Driver::find($driverId);
        if (!$driver) {
            throw new \Exception('Driver not found.');
        }

        // Get product
        $product = \App\Models\Product::find($productId);
        if (!$product) {
            throw new \Exception('Product not found.');
        }

        // Prepare transaction data
        $transactionData = [
            'driver_id' => $driverId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'type' => $type,
            'invoice_id' => $invoiceId ?? null,
            'remark' => $remark,
        ];

        try {
            // Create the transaction
            $transaction = self::create($transactionData);
            
            return $transaction;
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to create inventory transaction: ' . $e->getMessage());
        }
    }



 
}