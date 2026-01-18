<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class ProductCategory
 * @package App\Models
 * @version [Current Date]
 *
 * @property string $name
 * @property integer $status
 */
class ProductCategory extends Model
{
    use HasFactory;

    public $table = 'product_categories';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'name',
        'status'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'status' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:255|unique:product_categories,name',
        'status' => 'required|integer|in:0,1',
    ];

    /**
     * Get all products in this category
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for available categories (for dropdowns)
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 1)->orderBy('name');
    }

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
}