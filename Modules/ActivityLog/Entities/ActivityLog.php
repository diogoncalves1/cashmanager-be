<?php
namespace Modules\ActivityLog\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Entities\User;

// use Modules\FinancialGoal\Database\Factories\FinancialGoalActivityFactory;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table   = 'activity_logs';
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['subject_id', 'user_id', 'type', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // protected static function newFactory(): FinancialGoalActivityFactory
    // {
    //     // return FinancialGoalActivityFactory::new();
    // }
}
