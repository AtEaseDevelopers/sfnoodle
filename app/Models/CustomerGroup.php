<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * Class CustomerGroup
 * @package App\Models
 * @version [Current Date]
 *
 * @property string $name
 * @property string $description
 * @property array $customer_ids_with_sequence
 */
class CustomerGroup extends Model
{
    use SoftDeletes;
    use HasFactory;

    public $table = 'customer_groups';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'name',
        'description',
        'customer_ids', // Will store as array of objects: [{"id": 1, "sequence": 1}, {"id": 2, "sequence": 2}]
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'customer_ids' => 'array' // Store as JSON array of objects
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'customer_ids' => 'nullable|array',
        'customer_ids.*.id' => 'required|integer|exists:customers,id',
        'customer_ids.*.sequence' => 'nullable|integer|min:1',
        'created_at' => 'nullable',
        'updated_at' => 'nullable',
        'deleted_at' => 'nullable'
    ];

    /**
     * Get customers through the stored IDs (ordered by sequence)
     */
    public function customers()
    {
        $customerData = $this->customer_ids ?? [];
        if (empty($customerData)) {
            return Customer::where('id', 0); // Return empty query if no customers
        }
        
        // Extract customer IDs
        $customerIds = array_column($customerData, 'id');
        
        // Get customers and then sort by sequence in PHP
        return Customer::whereIn('id', $customerIds);
    }

    /**
     * Get all assignments for this group
     */
    public function assignments()
    {
        return $this->hasMany(Assign::class, 'customer_group_id');
    }

    /**
     * Get customer IDs with sequence as array
     */
    public function getCustomerIdsAttribute($value)
    {
        if (empty($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            
            // If it's a simple array of IDs, convert to new format
            if (is_array($decoded) && count($decoded) > 0 && !is_array($decoded[0])) {
                $newFormat = [];
                foreach ($decoded as $index => $customerId) {
                    $newFormat[] = [
                        'id' => $customerId,
                        'sequence' => $index + 1
                    ];
                }
                return $newFormat;
            }
            
            return is_array($decoded) ? $decoded : [];
        }
        
        // If it's already an array but in old format, convert it
        if (is_array($value) && count($value) > 0 && !isset($value[0]['id'])) {
            $newFormat = [];
            foreach ($value as $index => $customerId) {
                $newFormat[] = [
                    'id' => $customerId,
                    'sequence' => $index + 1
                ];
            }
            return $newFormat;
        }
        
        return is_array($value) ? $value : [];
    }

    /**
     * Set customer IDs with sequence as JSON
     */
    public function setCustomerIdsAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['customer_ids'] = json_encode([]);
        } else {
            // Ensure proper format: array of objects with id and sequence
            $formattedData = [];
            
            if (is_array($value)) {
                foreach ($value as $item) {
                    if (is_array($item) && isset($item['id'])) {
                        $formattedData[] = [
                            'id' => (int) $item['id'],
                            'sequence' => isset($item['sequence']) ? (int) $item['sequence'] : 1
                        ];
                    } elseif (is_numeric($item)) {
                        // Old format - just ID, assign default sequence
                        $formattedData[] = [
                            'id' => (int) $item,
                            'sequence' => count($formattedData) + 1
                        ];
                    }
                }
            }
            
            // Sort by sequence
            usort($formattedData, function($a, $b) {
                return $a['sequence'] <=> $b['sequence'];
            });
            
            $this->attributes['customer_ids'] = json_encode($formattedData);
        }
    }

    /**
     * Get customer IDs only (for backward compatibility)
     */
    public function getCustomerIdsOnlyAttribute()
    {
        $customerData = $this->customer_ids ?? [];
        return array_column($customerData, 'id');
    }

    /**
     * Get customers sorted by sequence
     */
    public function getCustomersSortedBySequence()
    {
        $customerData = $this->customer_ids ?? [];
        if (empty($customerData)) {
            return collect();
        }
        
        // Get all customers
        $customerIds = array_column($customerData, 'id');
        $customers = Customer::whereIn('id', $customerIds)->get()->keyBy('id');
        
        // Sort by sequence
        usort($customerData, function($a, $b) {
            return $a['sequence'] <=> $b['sequence'];
        });
        
        // Build sorted collection
        $sortedCustomers = collect();
        foreach ($customerData as $data) {
            if (isset($customers[$data['id']])) {
                $customer = $customers[$data['id']];
                $customer->sequence = $data['sequence']; // Add sequence to customer object
                $sortedCustomers->push($customer);
            }
        }
        
        return $sortedCustomers;
    }

    /**
     * Add or update a customer with sequence
     */
    public function addCustomerWithSequence($customerId, $sequence = null)
    {
        $customerData = $this->customer_ids ?? [];
        
        // Remove if already exists
        $customerData = array_filter($customerData, function($item) use ($customerId) {
            return $item['id'] != $customerId;
        });
        
        // Add new entry
        if ($sequence === null) {
            // Find next available sequence
            $maxSequence = 0;
            foreach ($customerData as $item) {
                if ($item['sequence'] > $maxSequence) {
                    $maxSequence = $item['sequence'];
                }
            }
            $sequence = $maxSequence + 1;
        }
        
        $customerData[] = [
            'id' => (int) $customerId,
            'sequence' => (int) $sequence
        ];
        
        // Sort by sequence
        usort($customerData, function($a, $b) {
            return $a['sequence'] <=> $b['sequence'];
        });
        
        $this->customer_ids = $customerData;
        return $this;
    }

    /**
     * Remove a customer from the group
     */
    public function removeCustomer($customerId)
    {
        $customerData = $this->customer_ids ?? [];
        $customerData = array_filter($customerData, function($item) use ($customerId) {
            return $item['id'] != $customerId;
        });
        
        // Re-sequence
        $newSequence = 1;
        foreach ($customerData as &$item) {
            $item['sequence'] = $newSequence++;
        }
        
        $this->customer_ids = array_values($customerData);
        return $this;
    }
}