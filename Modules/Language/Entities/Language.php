<?php
namespace Modules\Language\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Language\Database\Factories\LanguageFactory;

class Language extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['code', 'name'];

    protected static function newFactory(): LanguageFactory
    {
        return LanguageFactory::new ();
    }
}
