<?php

namespace Modules\Category\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Category\Casts\CategoryNameCast;
use Modules\User\Entities\User;

class Category extends Model
{
    /** @use HasFactory<\Modules\Category\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $table = 'categories';
    protected $fillable = ['name', 'type', 'icon', 'color', 'default', 'parent_id', 'user_id'];
    protected $casts = [
        'name' => CategoryNameCast::class
    ];

    protected static function newFactory()
    {
        return \Modules\Category\Database\Factories\CategoryFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function scopeDefault($query, $default)
    {
        return $query->where('default', $default);
    }
    public function scopeUserId($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
