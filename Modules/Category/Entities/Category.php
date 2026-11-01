<?php
namespace Modules\Category\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Category\Casts\CategoryNameCast;
use Modules\User\Entities\User;

class Category extends Model
{
    /** @use HasFactory<\Modules\Category\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $table    = 'categories';
    protected $fillable = ['name', 'type', 'icon', 'color', 'code', 'is_default', 'parent_id', 'user_id'];
    protected $casts    = [
        'name' => CategoryNameCast::class,
        'is_default' => 'boolean',
        'archived_at' => 'datetime',
    ];

    protected static function newFactory()
    {
        return \Modules\Category\Database\Factories\CategoryFactory::new ();
    }

    public const PERSONAL_LIMIT = 50;

    public const INTERNAL_CODES = ['financialGoalContribution', 'financialGoalWithdrawal'];

    public function localizedName(?string $locale = null): string
    {
        $name = $this->name;
        if (is_string($name)) {
            return $name;
        }

        $translations = (array) $name;

        return (string) ($translations[$locale ?? app()->getLocale()] ?? $translations['en'] ?? reset($translations) ?: '');
    }

    public function scopeVisibleTo($query, $userId)
    {
        return $query->where(fn ($query) => $query->where('is_default', true)->orWhere('user_id', $userId));
    }

    public function scopeSelectable($query, $userId)
    {
        return $query->visibleTo($userId)->whereNull('archived_at')
            ->where(fn ($query) => $query->whereNull('code')->orWhereNotIn('code', self::INTERNAL_CODES));
    }

    public function transactions()
    {
        return $this->hasMany(\Modules\Accounts\Entities\Transaction::class);
    }

    public function recurrings()
    {
        return $this->hasMany(\Modules\RecurringTransaction\Entities\RecurringTransaction::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function scopeDefault($query, $default)
    {
        return $query->where('is_default', $default);
    }
    public function scopeUserId($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
