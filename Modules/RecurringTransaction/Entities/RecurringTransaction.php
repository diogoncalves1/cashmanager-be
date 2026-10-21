<?php

namespace Modules\RecurringTransaction\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Accounts\Entities\Account;
use Modules\Accounts\Entities\Transaction;
use Modules\Category\Entities\Category;
use Modules\RecurringTransaction\Database\Factories\RecurringTransactionFactory;
use Modules\User\Entities\User;

class RecurringTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id', 'category_id', 'user_id', 'title',
        'type', 'amount', 'description', 'frequency', 'custom_frequency_days',
        'start_date', 'end_date', 'last_generated_at', 'is_active',
        'is_linked', 'linked_type', 'linked_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_generated_at' => 'date',
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
        'is_linked' => 'boolean',
    ];

    protected static function newFactory()
    {
        return RecurringTransactionFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'recurring_transaction_id');
    }

    public function linked()
    {
        return $this->morphTo('linked');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForAccount($query, $id)
    {
        return $query->where('account_id', $id);
    }

    public function nextDateAfter(Carbon $from): Carbon
    {
        return match ($this->frequency) {
            'daily' => $from->copy()->addDay(),
            'weekly' => $from->copy()->addWeek(),
            'monthly' => $from->copy()->addMonth(),
            'yearly' => $from->copy()->addYear(),
            'custom' => $from->copy()->addDays($this->custom_frequency_days),
        };
    }

    public function getNextOccurrenceAttribute(): ?string
    {
        $next = $this->last_generated_at
            ? $this->nextDateAfter($this->last_generated_at)
            : $this->start_date?->copy();

        if (! $next || ($this->end_date && $next->gt($this->end_date))) {
            return null;
        }

        return $next->toDateString();
    }

    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }
}
