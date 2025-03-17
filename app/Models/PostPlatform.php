<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostPlatform extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;

    const ID = 'id';
    const POST_ID = 'post_id';
    const SOCIAL_ACCOUNT_ID = 'social_account_id';
    const PLATFORM = 'platform';
    const STATUS = 'status';
    const POST_AT = 'posted_at';
    const CREATED_AT = 'created_at';

    protected $fillable = [
        self::POST_ID,
        self::SOCIAL_ACCOUNT_ID,
        self::PLATFORM,
        self::STATUS,
        self::POST_AT,
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, self::POST_ID, 'id');
    }

    public function socialAccount()
    {
        return $this->belongsTo(SocialAccount::class, self::SOCIAL_ACCOUNT_ID, 'id');
    }
}
