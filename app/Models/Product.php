<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Product
 * @package App\Models
 * @version June 20, 2023, 6:43 pm +08
 *
 * @property string $code
 * @property string $name
 * @property number $price
 * @property integer $status
 * @property string $category
 * @property string $image_path
 * @property string $uom
 * @property json $tiered_pricing
 */
class Product extends Model
{
    // use SoftDeletes;
    use HasFactory;

    public $table = 'products';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'code',
        'name',
        'price',
        'uom',
        'status',
        'category',
        'image_path',
        'tiered_pricing' // Add this
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'name' => 'string',
        'price' => 'float',
        'uom' => 'string',
        'status' => 'integer',
        'category' => 'string',
        'image_path' => 'string',
        'tiered_pricing' => 'array' // Cast JSON to array
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'code' => 'required|string|max:255|unique:products,code',
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'category' => 'nullable|string|max:255',
        'status' => 'required|integer|in:0,1',
        'uom' => 'required|string|max:50',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'tiered_pricing' => 'nullable|array',
        'tiered_pricing.*.quantity' => 'required|integer|min:1',
        'tiered_pricing.*.price' => 'required|numeric|min:0'
    ];

    /**
     * Get status options
     */
    public static function getStatusOptions()
    {
        return [
            1 => 'Active',
            0 => 'Inactive'
        ];
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->status == 1 ? 'badge-success' : 'badge-danger';
    }

    /**
     * Get category name (now returns the category string directly)
     */
    public function getCategoryNameAttribute()
    {
        return $this->category ?: 'N/A';
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path && file_exists(public_path($this->image_path))) {
            return asset($this->image_path);
        }
        return asset('images/no-image.png'); // Default no-image placeholder
    }

    /**
     * Get image HTML for display
     */
    public function getImageHtmlAttribute($width = 100, $height = 100)
    {
        $url = $this->image_url;
        return "<img src='{$url}' width='{$width}' height='{$height}' style='object-fit: cover;' alt='{$this->name}'>";
    }

    /**
     * Get tiered pricing as array
     */
    public function getTieredPricingAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        
        $pricing = json_decode($value, true);
        
        // Sort by quantity ascending
        usort($pricing, function($a, $b) {
            return $a['quantity'] - $b['quantity'];
        });
        
        return $pricing;
    }

    /**
     * Set tiered pricing
     */
    public function setTieredPricingAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['tiered_pricing'] = null;
            return;
        }
        
        // Remove empty rows
        $value = array_filter($value, function($item) {
            return !empty($item['quantity']) && !empty($item['price']);
        });
        
        if (empty($value)) {
            $this->attributes['tiered_pricing'] = null;
            return;
        }
        
        // Sort by quantity
        usort($value, function($a, $b) {
            return $a['quantity'] - $b['quantity'];
        });
        
        $this->attributes['tiered_pricing'] = json_encode($value);
    }

    /**
     * Calculate price based on quantity
     * 
     * @param int $quantity
     * @return float
     */
    public function calculatePrice($quantity)
    {
        $tieredPricing = $this->tiered_pricing;
        
        if (empty($tieredPricing)) {
            return $this->price * $quantity;
        }
        
        // Find applicable tier (largest quantity that's <= requested quantity)
        $applicableTier = null;
        foreach ($tieredPricing as $tier) {
            if ($quantity >= $tier['quantity']) {
                $applicableTier = $tier;
            } else {
                break;
            }
        }
        
        if ($applicableTier) {
            return $applicableTier['price'] * $quantity;
        }
        
        return $this->price * $quantity;
    }

    /**
     * Get formatted tiered pricing for display
     */
    public function getFormattedTieredPricingAttribute()
    {
        $pricing = $this->tiered_pricing;
        if (empty($pricing)) {
            return null;
        }
        
        $html = '<table class="table table-sm table-bordered">';
        $html .= '<thead><tr><th>Min Quantity</th><th>Price per Unit</th><th>Total for Quantity</th></tr></thead>';
        $html .= '<tbody>';
        
        foreach ($pricing as $tier) {
            $totalPrice = $tier['price'] * $tier['quantity'];
            $html .= "<tr>";
            $html .= "<td>≥ {$tier['quantity']} units</td>";
            $html .= "<td>" . number_format($tier['price'], 2) . "</td>";
            $html .= "<td>" . number_format($totalPrice, 2) . "</td>";
            $html .= "</tr>";
        }
        
        $html .= '</tbody></table>';
        
        return $html;
    }
}