<?php
namespace Modules\Asset\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["ticker", "name", "country", "exchange", "sector", "website", "logo",
        "price_json", "fundamentals_json", "dividends_json", "dividends_history_json", "has_dividends",
        "price", "price_start_year", "price_last_days_history_json", "currency", "news_json",
        "type", "last_update", "last_update_dividends_info", "last_update_news", "custom_fields"];

    protected $casts = [
        'custom_fields'                => 'array',
        'dividends_json'               => 'array',
        'fundamentals_json'            => 'array',
        'dividends_history_json'       => 'array',
        'price_json'                   => 'array',
        'price_last_days_history_json' => 'array',
        'news_json'                    => 'array',
    ];

}
