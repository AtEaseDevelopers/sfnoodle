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
        'category', // Changed from category_id
        'uom'
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
        'category' => 'string' // Changed from category_id
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
        'category' => 'nullable|string|max:255', // Changed validation
        'status' => 'required|integer|in:0,1',
        'uom' => 'required|string|max:50',
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
}