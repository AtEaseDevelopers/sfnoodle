<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Driver extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'drivers';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'employeeid',
        'password',
        'name',
        'invoice_code',
        'status',
        'trip_id',
        'remark',
        'session',
        'credit_amount'
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'employeeid' => 'string',
        'password' => 'string',
        'id' => 'integer',
        'name' => 'string',
        'status' => 'integer',
        'remark' => 'string',
        'session' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'employeeid' => 'required|string|max:20|unique:drivers,employeeid',
        'password' => 'required|string|max:65535',
        'name' => 'required|string|max:255',
        'status' => 'required',
        'invoice_code' => 'required|string|max:10|unique:drivers,invoice_code',
        'remark' => 'nullable|string|max:255',
    ];

    public function getFirstVaccineAttribute($value)
    {
        if($value != ''){
            return Carbon::parse($value)->format('d-m-Y');
        }else{
            return '';
        }
    }

    public function getSecondVaccineAttribute($value)
    {
        if($value != ''){
            return Carbon::parse($value)->format('d-m-Y');
        }else{
            return '';
        }
    }
     public function inventoryBalances()
    {
        return $this->hasMany(\App\Models\InventoryBalance::class, 'driver_id', 'id');
    }

}
