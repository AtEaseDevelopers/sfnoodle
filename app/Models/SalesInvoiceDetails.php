<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceDetails extends Model
{
    use HasFactory;

    public $table = 'sales_invoice_details';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'sales_invoice_id',
        'product_id',
        'quantity',
        'price',
        'totalprice',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'sales_invoice_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'float',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'sales_invoice_id' => 'required',
        'product_id' => 'required',
        'quantity' => 'required|integer',
        'price' => 'required|numeric',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(\App\Models\SalesInvoice::class, 'sales_invoice_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }

}
