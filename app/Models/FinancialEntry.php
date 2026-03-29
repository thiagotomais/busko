<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialEntry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'guardian_id',
        'passenger_id',
        'competence_month',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'competence_month' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the tenant of the financial entry.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the guardian linked to the billing entry.
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class);
    }

    /**
     * Get the passenger linked to the billing entry.
     */
    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    /**
     * Return effective status considering overdue pending records.
     */
    public function effectiveStatus(): string
    {
        if ($this->status === 'pending' && $this->due_date && $this->due_date->isPast()) {
            return 'overdue';
        }

        return $this->status;
    }
}
