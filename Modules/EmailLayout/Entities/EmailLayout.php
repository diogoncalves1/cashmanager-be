<?php

namespace Modules\EmailLayout\Entities;

use Illuminate\Database\Eloquent\Model;

class EmailLayout extends Model
{
    protected $fillable = [
        'subject', 'email', 'signature', 'email_type_id', 'attach', 'attach_name'
    ];

    public function emailType()
    {
        return $this->belongsTo(EmailType::class);
    }

}
