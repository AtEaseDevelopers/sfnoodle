<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';
        
    // Status constants
    public const STATUS_COMPLETED = 0;
    public const STATUS_CANCELLED = 1;

    // Creator type constants 
    public const CREATOR_USER = 0;
    public const CREATOR_DRIVER = 1;

    const PAYMENT_TYPE_CASH = 1;
    const PAYMENT_TYPE_CREDIT = 2;

    protected $fillable = [
        'invoiceno',
        'date',
        'customer_id',
        'paymentterm',
        'status',
        'remark',
        'sales_invoice_id',
        'created_by',
        'is_driver',
        'trip_id',
        'driver_id'
    ];
    
    protected $casts = [
        'date' => 'datetime',
        'is_driver' => 'boolean',
        'created_by' => 'integer',
    ];

    public static $rules = [
        'invoiceno' => 'required|string|max:255|unique:invoices,invoiceno',
        'date' => 'required',
        'customer_id' => 'required',
        'paymentterm' => 'required',
        'status' => 'required',
        'remark' => 'nullable|string|max:255',
        'details' => 'required|array|min:1',
        'details.*.product_id' => 'required|exists:products,id',
        'details.*.quantity' => 'required|numeric|min:0.01',
        'details.*.price' => 'required|numeric|min:0',
    ];

    public static $Updaterules = [
        'invoiceno' => 'required|string|max:255|exists:invoices,invoiceno',
        'date' => 'required',
        'customer_id' => 'required',
        'paymentterm' => 'required',
        'status' => 'required',
        'remark' => 'nullable|string|max:255',
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
            // Set default status to PENDING if not provided
            if (!isset($model->status)) {
                $model->status = self::STATUS_COMPLETED;
            }
            
            // Set created_by and is_driver for admin portal
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->is_driver = false; // Admin portal creates as user
            }
        });
    }

    public static function generateInvoiceNumber($driver_id = null)
    {
        // Get current year and month
        $year = date('y'); // Last 2 digits of year
        $month = date('m'); // Month with leading zeros
        
        if ($driver_id) {
            $user = \App\Models\Driver::find($driver_id);
        } else {
            $user = Auth::user();
        }

        $userCode = $user->invoice_code ?? ''; // Default to R00 if not set
        
        // Get the latest invoice number for current month and user code
        $prefix = "AE{$year}{$month}/{$userCode}/";
        
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

    public static function getPaymentTypeOptions()
    {
        return [
            self::PAYMENT_TYPE_CASH => 'Cash',
            self::PAYMENT_TYPE_CREDIT => 'Credit',
        ];
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
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    /**
     * Get status as text
     *
     * @return string
     */
    public function getStatusTextAttribute()
    {
        $statuses = self::getStatusOptions();
        return $statuses[$this->status] ?? 'Unknown';
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
     * Get payment term as text
     *
     * @return string
     */
    public function getPaymentTermTextAttribute()
    {
        $paymentTerms = [
            'Cash' => 'Cash',
            'Credit' => 'Credit',
            'Online BankIn' => 'Online Bank In',
            'E-wallet' => 'E-wallet',
            'Cheque' => 'Cheque'
        ];
        
        return $paymentTerms[$this->paymentterm] ?? $this->paymentterm;
    }

    /**
     * Get the total amount from invoice details
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        // If total is already calculated and cached in a field, return it
        if (isset($this->attributes['total']) && $this->attributes['total'] !== null) {
            return (float) $this->attributes['total'];
        }
        
        // Calculate from related details
        if ($this->invoiceDetails && count($this->invoiceDetails) > 0) {
            return $this->invoiceDetails->sum(function($detail) {
                return (float) ($detail->totalprice ?? ($detail->quantity * $detail->price));
            });
        }
        
        // If there's no relationship loaded yet, query the database
        return (float) InvoiceDetail::where('invoice_id', $this->id)
            ->select(DB::raw('SUM(totalprice) as total'))
            ->value('total') ?? 0;
    }

    /**
     * Get total amount formatted with 2 decimal places
     *
     * @return string
     */
    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2);
    }

    /**
     * Get formatted date
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->date ? $this->date->format('d-m-Y H:i:s') : '';
    }

    /**
     * Scope a query to only include invoices created by driver.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDriver($query)
    {
        return $query->where('is_driver', true);
    }

    /**
     * Scope a query to only include invoices created by user/admin.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query)
    {
        return $query->where('is_driver', false);
    }

    /**
     * Scope a query to include total amount in query results
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTotal($query)
    {
        return $query->addSelect([
            'invoices.*',
            DB::raw('(
                SELECT SUM(totalprice) 
                FROM invoice_details 
                WHERE invoice_details.invoice_id = invoices.id
            ) as total')
        ]);
    }

    /**
     * Relationships
     */
    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id', 'id');
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    
    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class, 'invoice_id', 'id');
    }

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class, 'invoice_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }


    /**
     * Get formatted invoice number
     */
    public function getFormattedInvoiceNoAttribute()
    {
        return $this->invoiceno;
    }

    /**
     * Check if invoice has payments
     */
    public function hasPayments()
    {
        return $this->invoicePayments()->exists();
    }

    public function cancel()
    {
        DB::beginTransaction();
        
        try {
            // Store the old status before cancellation
            $oldStatus = $this->status;
            
            // Update invoice status to CANCELLED
            $this->status = self::STATUS_CANCELLED;
            $this->save();
            
            // If payment term is Cash, update associated invoice payments
            if ($this->paymentterm == 'Cash') {
                $invoicePayments = $this->invoicePayments;
                foreach ($invoicePayments as $payment) {
                    $payment->status = InvoicePayment::STATUS_CANCELLED; 
                    $payment->save();
                }
            }
            
            // If invoice was created by driver, add back inventory balance
            if ($this->is_driver) {
                $this->restoreDriverInventory();
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restore inventory balance for driver
     */
    private function restoreDriverInventory()
    {
        $driverId = $this->created_by;
        $invoiceDetails = $this->invoiceDetails;
        
        foreach ($invoiceDetails as $detail) {
            $productId = $detail->product_id;
            $quantity = $detail->quantity;
            
            // Find existing inventory balance for this driver and product
            $inventoryBalance = InventoryBalance::where([
                'driver_id' => $driverId,
                'product_id' => $productId
            ])->first();
            
            if ($inventoryBalance) {
                // Add back the quantity
                $inventoryBalance->quantity += $quantity;
                $inventoryBalance->save();
            } else {
                // Create new inventory balance record (though this shouldn't normally happen)
                InventoryBalance::create([
                    'driver_id' => $driverId,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
            }
        }
    }
}