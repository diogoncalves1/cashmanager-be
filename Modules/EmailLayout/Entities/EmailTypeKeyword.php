<?php

namespace Modules\EmailLayout\Entities;

use Illuminate\Database\Eloquent\Model;

class EmailTypeKeyword extends Model
{
    protected $table = 'email_type_keywords';
    protected $fillable = [
        'keyword', 'description', 'email_type_id'
    ];
}
