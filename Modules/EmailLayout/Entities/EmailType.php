<?php

namespace Modules\EmailLayout\Entities;

use Illuminate\Database\Eloquent\Model;

class EmailType extends Model
{
    protected $fillable = [
        'name', 'code', 'description'
    ];

    public function emailLayout()
    {
        return $this->hasMany(EmailLayout::class);
    }

    /**
     * Get the keywords for the emailType.
     */
    public function keywords()
    {
        return $this->hasMany(EmailTypeKeyword::class);
    }
}
