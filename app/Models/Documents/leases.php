<?php

namespace App\Models\Documents;

use \App\Models\AccessControlledModel as AccessControlledModel;
use App\Models\Customers\Customer;

/**
 * Class leases
 * @package App\Models
 * @version November 23, 2016, 9:28 pm UTC
 */
class leases extends AccessControlledModel
{

    public $table = 'leases';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';



    public $fillable = [
        'unique_lease_id',
        'reference_number',
        'terminating_user_id',
        'early_termination_reason',
        'early_termination_datetime',
        'document_decryption_key_ciphertext',
        'is_active',
        'is_terminated',
        'is_manual'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'unique_lease_id' => 'string',
        'reference_number' => 'string',
        'early_termination_reason' => 'string',
        'document_decryption_key_ciphertext' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function bedroomsLeases()
    {
        return $this->hasMany(\App\Models\BedroomsLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function customerEnvelopesLeases()
    {
        return $this->hasMany(\App\Models\CustomerEnvelopesLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     **/
    public function customers()
    {
        return $this->belongsToMany(Customer::class, '_customers_leases', 'lease_id', 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentInputFieldsLeases()
    {
        return $this->hasMany(\App\Models\DocumentInputFieldsLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentSetsLeases()
    {
        return $this->hasMany(\App\Models\DocumentSetsLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentSigningEventsLeases()
    {
        return $this->hasMany(\App\Models\DocumentSigningEventsLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentTemplatesLeases()
    {
        return $this->hasMany(\App\Models\DocumentTemplatesLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentVariablesLeases()
    {
        return $this->hasMany(\App\Models\DocumentVariablesLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentVersionsLeases()
    {
        return $this->hasMany(\App\Models\DocumentVersionsLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function documentsLeases()
    {
        return $this->hasMany(\App\Models\DocumentsLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function feesLeases()
    {
        return $this->hasMany(\App\Models\FeesLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leaseClassifiersLeases()
    {
        return $this->hasMany(\App\Models\LeaseClassifiersLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leaseTypesLeases()
    {
        return $this->hasMany(\App\Models\LeaseTypesLease::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leasesPaymentOptions()
    {
        return $this->hasMany(\App\Models\LeasesPaymentOption::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leasesSigningQueues()
    {
        return $this->hasMany(\App\Models\LeasesSigningQueue::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function leasesUnits()
    {
        return $this->hasMany(\App\Models\LeasesUnit::class);
    }
}
