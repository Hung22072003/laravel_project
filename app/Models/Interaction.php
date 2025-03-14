<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interaction extends Model
{
    use HasFactory, HasUuids;
    use SoftDeletes;

    const POST_PLATFORM_ID = "post_platform_id";
    const NUMBER_OF_LIKES = "number_of_likes";
    const NUMBER_OF_SHARES = "number_of_shares";
    const NUMBER_OF_COMMENTS = "number_of_comments";

    protected $fillable = [
        self::POST_PLATFORM_ID,
        self::NUMBER_OF_LIKES,
        self::NUMBER_OF_SHARES,
        self::NUMBER_OF_COMMENTS,
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function postPlatform()
    {
        return $this->belongsTo(PostPlatform::class);
    }
}
