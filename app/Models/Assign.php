<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Class Assign
 * @package App\Models
 * @version June 21, 2023, 6:30 pm +08
 *
 * @property integer $driver_id
 * @property integer $customer_id
 */
class Assign extends Model
{
    // use SoftDeletes;
    use HasFactory;

    public $table = 'assigns';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // protected $dates = ['deleted_at'];

    public $fillable = [
        'driver_id',
        'customer_group_id',
        'sequence'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'driver_id' => 'integer',
        'customer_group_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'driver_id' => 'required|exists:drivers,id',
        'customer_group_id' => 'required|exists:customer_groups,id',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * Get the driver
     */
    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id');
    }

    /**
     * Get the customer group
     */
    public function customerGroup()
    {
        return $this->belongsTo(\App\Models\CustomerGroup::class, 'customer_group_id');
    }

    /**
     * Get all customers through the customer group
     */
    public function customers()
    {
        return $this->customerGroup->customers();
    }
}