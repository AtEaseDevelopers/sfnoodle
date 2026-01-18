<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesInvoice extends Model
{
    // use SoftDeletes;

    use HasFactory;

    public $table = 'sales_invoices';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    // Status constants
    public const STATUS_PENDING = 0;
    public const STATUS_CANCELLED = 1;
    public const STATUS_CONVERTED_TO_INVOICE = 2;
    
    // Xero status constants
    public const STATUS_SYNCED_TO_XERO = 1;
    public const STATUS_VOIDED = 2;

    // Creator type constants
    public const CREATOR_USER = 0;
    public const CREATOR_DRIVER = 1;

    public $fillable = [
        'invoiceno',
        'date',
        'customer_id',
        'paymentterm',
        'status',
        'remark',
        'created_by',      // ID of creator (user or driver)
        'is_driver',       // Boolean: true if created by driver, false if by user
        'converted_to_invoice', // Boolean: true if converted to invoice
        'invoice_id',  // ID of the converted invoice (if converted)
        'trip_id',   // ID of the trip (if created by driver)
        'driver_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'invoiceno' => 'string',
        'date' => 'datetime:d-m-Y H:i:s',
        'customer_id' => 'integer',
        'paymentterm' => 'string',
        'status' => 'integer',
        'remark' => 'string',
        'created_by' => 'integer',
        'is_driver' => 'boolean',
        'converted_to_invoice' => 'boolean',
        'invoice_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'invoiceno' => 'required|string|max:255|unique:sales_invoices,invoiceno',
        'date' => 'required|date',
        'customer_id' => 'required|exists:customers,id',
        'paymentterm' => 'required|string',
        'status' => 'required|integer',
        'details' => 'required|array|min:1',
        'details.*.product_id' => 'required|exists:products,id',
        'details.*.quantity' => 'required|numeric|min:0.01',
        'details.*.price' => 'required|numeric|min:0',
    ];

    public static $Updaterules = [
        'invoiceno' => 'required|string|max:255|exists:sales_invoices,invoiceno',
        'date' => 'required|date',
        'customer_id' => 'required|exists:customers,id',
        'paymentterm' => 'required|string',
        'status' => 'required|integer',
        'details' => 'required|array|min:1',
        'details.*.product_id' => 'required|exists:products,id',
        'details.*.quantity' => 'required|numeric|min:0.01',
        'details.*.price' => 'required|numeric|min:0',
    ];

    /**
     * Boot method to set default values
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set default status to PENDING
            if (!isset($model->status)) {
                $model->status = self::STATUS_PENDING;
            }
            
            // Set created_by and is_driver for admin portal
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->is_driver = false; // Admin portal creates as user
            }
            
            // Set converted_to_invoice default to false
            if (!isset($model->converted_to_invoice)) {
                $model->converted_to_invoice = false;
            }
        });
    }

    public function getStatusTextAttribute()
    {
        $statuses = self::getStatusOptions();
        return $statuses[$this->status] ?? 'Unknown';
    }

    /**
     * Generate next invoice number
     * Format: SO2512/R08/0001
     * Where:
     * - SO = Fixed prefix for Sales Invoice
     * - 25 = Last 2 digits of year
     * - 12 = Month (2 digits)
     * - R08 = User invoice code (from auth user -> invoice_code)
     * - 0001 = Auto-incrementing invoice index
     *
     * @return string
     */
    public static function generateInvoiceNumber($driver_id = null)
    {
        // Get current year and month
        $year = date('y'); // Last 2 digits of year
        $month = date('m'); // Month with leading zeros

        // Get user's invoice code (assuming user has invoice_code field)
        if ($driver_id) {
            $user = \App\Models\Driver::find($driver_id);
        } else {
            $user = Auth::user();
        }

        $userCode = $user->invoice_code ?? ''; // Default to R00 if not set

        // Get the latest invoice number for current month and user code
        $prefix = "SO/{$year}{$month}/{$userCode}/";
        
        // Find the latest invoice with this prefix
        $latestInvoice = self::orderBy('id', 'desc')
            ->first();
        
        if ($latestInvoice) {
            // Extract the numeric part
            $invoiceNumber = $latestInvoice->invoiceno;
            $numericPart = (int) substr($invoiceNumber, strlen($prefix));
            $nextNumber = $numericPart + 1;
        } else {
            // Start from 1 for new month/user combination
            $nextNumber = 1;
        }
        
        // Format the number with leading zeros (minimum 4 digits, but can grow)
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $formattedNumber;
    }
    
    /**
     * Get the next invoice number without saving
     * This can be used to prefill the form
     *
     * @return string
     */
    public static function getNextInvoiceNumber($driver_id = null)
    {
        return self::generateInvoiceNumber($driver_id);
    }

    /**
     * Validate if an invoice number already exists
     *
     * @param string $invoiceNumber
     * @return bool
     */
    public static function invoiceNumberExists($invoiceNumber)
    {
        return self::where('invoiceno', $invoiceNumber)->exists();
    }

    /**
     * Get status options for dropdown
     *
     * @return array
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_CONVERTED_TO_INVOICE => 'Converted to Invoice',
        ];
    }

    /**
     * Check if sales invoice can be cancelled
     *
     * @return bool
     */
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING]);
    }

    /**
     * Check if sales invoice can be converted to invoice
     *
     * @return bool
     */
    public function canBeConvertedToInvoice()
    {
        return $this->status == self::STATUS_PENDING;
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Get creator (driver) relationship - when is_driver is true
     */
    public function createdByDriver()
    {
        return $this->belongsTo(Driver::class, 'created_by', 'id');
    }

    /**
     * Dynamic creator relationship accessor
     */
    public function getCreatorAttribute()
    {
        if ($this->is_driver) {
            return $this->createdByDriver;
        } else {
            return $this->createdByUser;
        }
    }

    /**
     * Get creator name
     *
     * @return string
     */
    public function getCreatorNameAttribute()
    {
        if (!$this->created_by) {
            return 'System';
        }
        
        if ($this->is_driver) {
            $driver = $this->createdByDriver;
            return $driver ? $driver->name : 'Unknown Driver';
        } else {
            $user = $this->createdByUser;
            return $user ? $user->name : 'Unknown User';
        }
    }

    /**
     * Get creator details (type and ID)
     */
    public function getCreatorDetailsAttribute()
    {
        if (!$this->created_by) {
            return [
                'type' => 'system',
                'id' => null,
                'name' => 'System'
            ];
        }
        
        if ($this->is_driver) {
            $driver = $this->createdByDriver;
            return [
                'type' => 'driver',
                'id' => $this->created_by,
                'name' => $driver ? $driver->name : 'Unknown Driver',
                'email' => $driver ? $driver->email : null,
                'phone' => $driver ? $driver->phone : null
            ];
        } else {
            $user = $this->createdByUser;
            return [
                'type' => 'user',
                'id' => $this->created_by,
                'name' => $user ? $user->name : 'Unknown User',
                'email' => $user ? $user->email : null
            ];
        }
    }


    /**
     * Convert sales invoice to invoice
     *
     * @return \App\Models\Invoice|null
     */
    public function convertToInvoice($driver_id = null, $details = [])
    {
        if (!$this->canBeConvertedToInvoice()) {
            return null;
        }

        DB::beginTransaction();
        $driver = $driver_id ? Driver::find($driver_id) : null;
        try {
            // Generate invoice number with prefix
            $invoiceNumber = $this->generateInvoiceNumberForConversion($driver_id);

            // Create the invoice
            $invoice = new \App\Models\Invoice();
            $invoice->invoiceno = $invoiceNumber;
            $invoice->date = $this->date;
            $invoice->customer_id = $this->customer_id;
            $invoice->paymentterm = $this->paymentterm;
            $invoice->status = Invoice::STATUS_COMPLETED; 
            $invoice->remark = $this->remark;
            $invoice->sales_invoice_id = $this->id; // Link back to original sales invoice
            $invoice->created_by = $driver_id ?? Auth::id();
            $invoice->is_driver = $driver_id ? true : false; 
            $invoice->trip_id = $driver_id ? $driver->trip_id : null;
            $invoice->driver_id = $driver_id ?? 0;
            $invoice->save();
            
            // Copy invoice details from $details array
                foreach ($details as $detail) {
                    // Calculate total price if not provided
                    $totalPrice = isset($detail['totalprice']) 
                        ? $detail['totalprice'] 
                        : ($detail['quantity'] * $detail['price']);

                    $invoiceDetail = new \App\Models\InvoiceDetail();
                    $invoiceDetail->invoice_id = $invoice->id;
                    $invoiceDetail->product_id = $detail['product_id'];
                    $invoiceDetail->quantity = $detail['quantity'];
                    $invoiceDetail->price = $detail['price'];
                    $invoiceDetail->totalprice = $totalPrice;
                    $invoiceDetail->remark = $detail['remark'] ?? null;
                    $invoiceDetail->save();
                }

            // Update sales invoice
            $this->converted_to_invoice = true;
            $this->invoice_id = $invoice->id;
            $this->status = self::STATUS_CONVERTED_TO_INVOICE;
            $this->save();
            
            DB::commit();
            
            return $invoice;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Generate invoice number for conversion (AE prefix)
     *
     * @return string
     */
    private function generateInvoiceNumberForConversion($driver_id = null)
    {
        // Get current year and month
        $year = date('y');
        $month = date('m');
        
        // Get user's invoice code
        $user = Auth::user();
        
         if ($driver_id) {
            $user = \App\Models\Driver::find($driver_id);
        } else {
            $user = Auth::user();
        }
        
        $userCode = $user->invoice_code ?? '';
    
        // Check existing invoices with AE prefix
        $prefix = "AE{$year}{$month}/{$userCode}/";
        
        $latestInvoice = \App\Models\Invoice::orderBy('id', 'desc')
            ->first();
        
        if ($latestInvoice) {
            $numericPart = (int) substr($latestInvoice->invoiceno, strlen($prefix));
            $nextNumber = $numericPart + 1;
        } else {
            $nextNumber = 1;
        }
        
        $formattedNumber = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $formattedNumber;
    }

    /**
     * Cancel the sales invoice
     *
     * @param string|null $reason
     * @return bool
     */
    public function cancel($reason = null)
    {
        if (!$this->canBeCancelled()) {
            return false;
        }
        
        $this->status = self::STATUS_CANCELLED;
        if ($reason) {
            $this->remark = $this->remark ? $this->remark . " [Cancelled: $reason]" : "Cancelled: $reason";
        }
        
        return $this->save();
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id', 'id');
    }

    public function salesInvoiceDetails()
    {
        return $this->hasMany(\App\Models\SalesInvoiceDetails::class, 'sales_invoice_id');
    }

    public function invoicepayment()
    {
        return $this->hasMany(\App\Models\InvoicePayment::class);
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class, 'invoice_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i:s');
    }
    
    /**
     * Scope a query to only include pending invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
    
    
    /**
     * Scope a query to only include cancelled invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }
    
    /**
     * Scope a query to only include convertible invoices.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConvertible($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                    ->where('converted_to_invoice', false);
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2);
    }

    public function getTotalAttribute()
    {
        // If total is already calculated and cached in a field, return it
        if (isset($this->attributes['total']) && $this->attributes['total'] !== null) {
            return (float) $this->attributes['total'];
        }
        
        // Calculate from related details
        if ($this->salesInvoiceDetails && count($this->salesInvoiceDetails) > 0) {
            return $this->salesInvoiceDetails->sum(function($detail) {
                return (float) ($detail->totalprice ?? ($detail->quantity * $detail->price));
            });
        }
        
        // If there's no relationship loaded yet, query the database
        return (float) SalesInvoiceDetails::where('sales_invoice_id', $this->id)
            ->select(DB::raw('SUM(totalprice) as total'))
            ->value('total') ?? 0;
    }
}