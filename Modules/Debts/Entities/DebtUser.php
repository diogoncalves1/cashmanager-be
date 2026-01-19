<?php
namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Modules\Debts\Database\Factories\DebtUserFactory;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

class DebtUser extends Pivot
{
    /** @use HasFactory<\Modules\Debts\Database\Factories\DebtUserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'debt_id', 'shared_role_id'];
    protected $table    = "debt_users";

    protected static function newFactory(): DebtUserFactory
    {
        return DebtUserFactory::new ();
    }

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function sharedRole()
    {
        return $this->belongsTo(SharedRole::class);
    }
}
