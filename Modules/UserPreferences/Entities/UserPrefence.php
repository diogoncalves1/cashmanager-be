<?php

namespace Modules\UserPreferences\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Modules\Currency\Entities\Currency;
use Modules\User\Entities\User;

class UserPrefence extends Model
{
    /** @use HasFactory<\Modules\UserPreferences\Database\Factories\UserPreferencesFactory> */
    use HasFactory;

    protected $table = "user_preferences";
    protected $fillable = ["user_id", "currency_id", "lang"];
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    public $timestamps = true;

    protected static function newFactory()
    {
        return \Modules\UserPreferences\Database\Factories\UserPreferencesFactoryFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
