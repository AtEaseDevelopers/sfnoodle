<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Models\Code;

class Customer extends Model
{
    // use SoftDeletes;
    use HasFactory;

    public $table = 'customers';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $fillable = [
        'code',
        'company',
        'paymentterm',
        'phone',
        'address',
        'status',
        'sst',
        'tin',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'company' => 'string',
        'phone' => 'string',
        'address' => 'string',
        'status' => 'integer',
        'sst' => 'string',
        'tin' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'code' => 'required|string|max:255|unique:customers,code',
        'company' => 'required|string|max:255|string|max:255',
        'paymentterm' => 'required',
        'phone' => 'nullable|string|max:20|nullable|string|max:20',
        'address' => 'nullable|string|max:65535|nullable|string|max:65535',
        'status' => 'required',
        'sst' => 'nullable|string|max:255',
        'tin' => 'nullable|string|max:255',
        'created_at' => 'nullable|nullable',
        'updated_at' => 'nullable|nullable'
    ];

    /**
     * Get the customer groups that this customer belongs to
     */
    public function customerGroups()
    {
        return CustomerGroup::whereJsonContains('customer_ids', $this->id);
    }

    /**
     * Accessor to get customer group names as comma-separated string
     */
    public function getCustomerGroupsAttribute()
    {
        $groups = $this->customerGroups()->get();
        return $groups->pluck('name')->implode(', ');
    }

    /**
     * Scope to eager load customer groups
     */
    public function scopeWithCustomerGroups($query)
    {
        return $query->with(['customerGroups' => function($q) {
            $q->select('id', 'name');
        }]);
    }
}