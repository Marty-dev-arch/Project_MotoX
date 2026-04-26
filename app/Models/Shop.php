<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'owner_name',
    'contact_number',
    'default_labor_rate',
    'currency_code',
    'auto_assign_job_orders',
    'notify_low_stock_alerts',
    'notify_job_order_updates',
    'notify_billing_updates',
])]
class Shop extends Model
{
    use HasFactory;

    
    protected function casts(): array
    {
        return [
            'default_labor_rate' => 'decimal:2',
            'auto_assign_job_orders' => 'boolean',
            'notify_low_stock_alerts' => 'boolean',
            'notify_job_order_updates' => 'boolean',
            'notify_billing_updates' => 'boolean',
        ];
    }

    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
    public function parts(): HasMany
    {
        return $this->hasMany(Part::class);
    }

    
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    
    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }
}
