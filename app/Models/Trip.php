<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Trip extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'trips';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    const START_TRIP = 1;
    const END_TRIP = 0;

    public $fillable = [
        'uuid', // Add this column
        'date',
        'driver_id',
        'type',
        'stock_data'
    ];

    protected $casts = [
        'id' => 'integer',
        'uuid' => 'integer',
        'date' => 'datetime:Y-m-d H:i:s', 
        'driver_id' => 'integer',
        'type' => 'integer'
    ];

    public static $rules = [
        'date' => 'required',
        'driver_id' => 'required',
        'type' => 'required',
    ];

    protected static function boot()
    {
        parent::boot();
        
    }

    /**
     * Generate unique integer reference
     */
    public static function generateUniqueReference()
    {
        do {
            // Generate a random integer (adjust range as needed)
            $reference = mt_rand(100000000, 999999999); // 9-digit number
            
            // Add timestamp prefix for more uniqueness
            // $reference = (int) (time() . mt_rand(1000, 9999));
            
        } while (self::where('uuid', $reference)->exists());
        
        return $reference;
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class, 'driver_id', 'id');
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
}