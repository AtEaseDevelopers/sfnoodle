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
        'image_path' // Add this
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
        'image_path' => 'string' // Add this
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
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // Add validation for image upload
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
}