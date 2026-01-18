<?php

namespace App\Repositories;

use App\Models\CustomerGroup;
use App\Repositories\BaseRepository;

/**
 * Class CustomerGroupRepository
 * @package App\Repositories
 * @version [Current Date]
*/

class CustomerGroupRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'description',
        'customer_ids'
    ];

    /**
     * Return searchable fields
     *
     * @return array
     */
    public function getFieldsSearchable()
    {
        return $this->fieldSearchable;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return CustomerGroup::class;
    }
}