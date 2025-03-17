<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;

    const ID = "id";
    const USER_ID = "user_id";
    const CONTENT = "content";
    const MEDIA_URLS = "media_urls";
    const SCHEDULED_TIME = "scheduled_time";

    public const STATUSES = [
        'SUCCESS' => 'SUCCESS',
        'FAILED' => 'FAILED',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'media_urls' => 'array',
    ];

    protected $fillable = [
        self::USER_ID,
        self::CONTENT,
        self::MEDIA_URLS,
        self::SCHEDULED_TIME,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function postPlatforms()
    {
        return $this->hasMany(PostPlatform::class); 
    }
}
