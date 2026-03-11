<?php
namespace Modules\User\Entities;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Accounts\Entities\AccountsView;
use Modules\Permission\Entities\Role;
use Modules\UserPreferences\Entities\UserPrefence;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'username_updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function newFactory()
    {
        return \Modules\User\Database\Factories\UserFactory::new ();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'username_updated_at' => 'datetime',
            'email_verified_at'   => 'datetime',
            'password'            => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function preferences()
    {
        return $this->hasOne(UserPrefence::class);
    }

    public function getBalance(): float
    {
        return (float) AccountsView::from('accounts_view as a')
            ->join('account_users as au', 'au.account_id', '=', 'a.id')
            ->join('shared_roles as sr', 'sr.id', '=', 'au.shared_role_id')
            ->join('shared_permission_roles as spr', 'spr.shared_role_id', '=', 'sr.id')
            ->join('shared_permissions as sp', 'sp.id', '=', 'spr.shared_permission_id')
            ->join('currencies as account_currency', 'a.currencyId', '=', 'account_currency.id')
            ->join('user_preferences as up', 'up.user_id', '=', 'au.user_id')
            ->join('currencies as user_currency', 'user_currency.id', '=', 'up.currency_id')
            ->where('au.user_id', $this->id)
            ->where('sp.code', 'updateUserBalance')
            ->selectRaw('
            COALESCE(
                SUM(a.balance * (user_currency.rate / account_currency.rate)),
                0
            ) as userBalance
        ')
            ->value('userBalance');

        // return Cache::remember(
        //     "user_balance_{$this->id}",
        //     now()->addMinutes(10),
        //     function () {
        //         return (float) $this->calculateBalance();
        //     }
        // );
    }

    public function calculateBalance(): float
    {
        return (float) AccountsView::from('accounts_view as a')
            ->join('account_users as au', 'au.account_id', '=', 'a.id')
            ->join('shared_roles as sr', 'sr.id', '=', 'au.shared_role_id')
            ->join('shared_permission_roles as spr', 'spr.shared_role_id', '=', 'sr.id')
            ->join('shared_permissions as sp', 'sp.id', '=', 'spr.shared_permission_id')
            ->join('currencies as account_currency', 'a.currencyId', '=', 'account_currency.id')
            ->join('user_preferences as up', 'up.user_id', '=', 'au.user_id')
            ->join('currencies as user_currency', 'user_currency.id', '=', 'up.currency_id')
            ->where('au.user_id', $this->id)
            ->where('sp.code', 'updateUserBalance')
            ->selectRaw('
            COALESCE(
                SUM(a.balance * (user_currency.rate / account_currency.rate)),
                0
            ) as userBalance
        ')
            ->value('userBalance');
    }
}
