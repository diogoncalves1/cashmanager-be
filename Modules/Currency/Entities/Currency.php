<?php

namespace Modules\Currency\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    /** @use HasFactory<\Database\Factories\CurrencyFactory> */
    use HasFactory;

    protected $table = "currencies";
    protected $fillable = ['code', 'name', 'symbol', 'rate'];
    protected $casts = [
        'name' => 'object'
    ];

    protected static function newFactory()
    {
        return \Modules\Currency\Database\Factories\CurrencyFactory::new();
    }

    public function scopeCode($query, $code)
    {
        return $query->where("code", $code);
    }
}
