<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Foc extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'focs';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $attributes = [
        'achievequantity' => 0,
    ];

    public $fillable = [
        'product_id',
        'customer_id',
        'achievequantity',
        'quantity',
        'free_product_id',
        'free_quantity',
        'startdate',
        'enddate',
        'status'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'customer_id' => 'integer',
        'achievequantity' => 'integer',
        'quantity' => 'integer',
        'free_product_id' => 'integer',
        'free_quantity' => 'integer',
        'startdate' => 'date:d-m-Y',
        'enddate' => 'date:d-m-Y',
        'status' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'product_id' => 'required',
        'customer_id' => 'required',
        'quantity' => 'required|numeric|numeric',
        'free_product_id' => 'required',
        'free_quantity' => 'required|numeric|numeric',
        'startdate' => 'required',
        'enddate' => 'required',
        'status' => 'required',
        'created_at' => 'nullable|nullable',
        'updated_at' => 'nullable|nullable'
    ];

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id', 'id');
    }

    public function freeproduct()
    {
        return $this->belongsTo(\App\Models\Product::class, 'free_product_id', 'id');
    }

    public function getStartDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getEndDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    /**
     * Calculate FOC items based on a specific date
     *
     * @param int $customerId
     * @param array $purchasedItems
     * @param string|Carbon $date The date to check FOC validity (default: now)
     * @return array
     */
    public static function calculateFocItems($customerId, $purchasedItems, $date = null)
    {
        $focItems = [];
        
        // Use provided date or current date if not specified
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        
        // Get all active FOC rules for this customer where the check date is within validity period
        $focRules = Foc::where('customer_id', $customerId)
            ->where('status', 1) // Active status
            ->where('startdate', '<=', $checkDate)
            ->where('enddate', '>=', $checkDate)
            ->get();
        
        if ($focRules->isEmpty()) {
            return $focItems;
        }
        
        // Convert purchased items to associative array for easy lookup
        $purchasedQuantities = [];
        foreach ($purchasedItems as $item) {
            $purchasedQuantities[$item['product_id']] = ($purchasedQuantities[$item['product_id']] ?? 0) + $item['quantity'];
        }
        
        foreach ($focRules as $rule) {
            // Check if the purchased product exists in the order
            $purchasedQty = $purchasedQuantities[$rule->product_id] ?? 0;
            
            // Calculate how many times the target quantity is achieved
            if ($purchasedQty >= $rule->quantity) {
                // Calculate number of FOC items to give
                $focMultiplier = floor($purchasedQty / $rule->quantity);
                $focQuantity = $focMultiplier * $rule->free_quantity;
                
                // Check if there's a maximum limit (if achievequantity is set)
                if ($rule->achievequantity > 0) {
                    $maxAllowed = floor($rule->achievequantity / $rule->quantity) * $rule->free_quantity;
                    $focQuantity = min($focQuantity, $maxAllowed);
                }
                
                if ($focQuantity > 0) {
                    $product = Product::find($rule->free_product_id);
                    
                    $focItems[] = [
                        'product_id' => $rule->free_product_id,
                        'quantity' => $focQuantity,
                        'product_code' => $product ? $product->code : 'N/A',
                        'product_name' => $product ? $product->name : 'Unknown Product',
                        'original_product_id' => $rule->product_id,
                        'original_quantity' => $purchasedQty,
                        'foc_rule_id' => $rule->id,
                        'is_foc' => true,
                        'price' => 0,
                        'totalprice' => 0
                    ];
                }
            }
        }
        
        return $focItems;
    }  
    
    /**
     * Get items with FOC based on a specific date
     *
     * @param int $customerId
     * @param array $purchasedItems
     * @param string|Carbon $date The date to check FOC validity
     * @return array
     */
    public static function getItemsWithFoc($customerId, $purchasedItems, $date = null)
    {
        $focItems = self::calculateFocItems($customerId, $purchasedItems, $date);
        
        // Prepare purchased items (mark as non-FOC)
        $allItems = [];
        foreach ($purchasedItems as $item) {
            $product = Product::find($item['product_id']);
            $allItems[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'product_code' => $product ? $product->code : 'N/A',
                'product_name' => $product ? $product->name : 'Unknown Product',
                'price' => $item['price'] ?? ($product ? $product->price : 0),
                'totalprice' => ($item['price'] ?? ($product ? $product->price : 0)) * $item['quantity'],
                'is_foc' => false
            ];
        }
        
        // Add FOC items
        foreach ($focItems as $focItem) {
            $allItems[] = $focItem;
        }
        
        return $allItems;
    }
    
    /**
     * Check if a specific product qualifies for FOC on a specific date
     *
     * @param int $customerId
     * @param int $productId
     * @param int $quantity
     * @param string|Carbon $date The date to check FOC validity
     * @return array|null
     */
    public static function checkProductFoc($customerId, $productId, $quantity, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        
        $rule = Foc::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->where('status', 1)
            ->where('startdate', '<=', $checkDate)
            ->where('enddate', '>=', $checkDate)
            ->first();
        
        if (!$rule) {
            return null;
        }
        
        if ($quantity >= $rule->quantity) {
            $focMultiplier = floor($quantity / $rule->quantity);
            $focQuantity = $focMultiplier * $rule->free_quantity;
            
            if ($rule->achievequantity > 0) {
                $maxAllowed = floor($rule->achievequantity / $rule->quantity) * $rule->free_quantity;
                $focQuantity = min($focQuantity, $maxAllowed);
            }
            
            if ($focQuantity > 0) {
                $product = Product::find($rule->free_product_id);
                return [
                    'product_id' => $rule->free_product_id,
                    'quantity' => $focQuantity,
                    'product_code' => $product ? $product->code : 'N/A',
                    'product_name' => $product ? $product->name : 'Unknown Product',
                    'price' => 0,
                    'totalprice' => 0,
                    'is_foc' => true
                ];
            }
        }
        
        return null;
    }
}