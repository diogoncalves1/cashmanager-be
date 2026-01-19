<?php
namespace Modules\Debts\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Debts\Database\Factories\DebtUserInviteFactory;

class DebtUserInvite extends Model
{
    /** @use HasFactory<\Modules\Detbs\Database\Factories\DebtUserInviteFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'debt_id', 'shared_role_id', 'status'];
    protected $table    = "debt_user_invites";

    protected static function newFactory(): DebtUserInviteFactory
    {
        return DebtUserInviteFactory::new ();
    }
}
