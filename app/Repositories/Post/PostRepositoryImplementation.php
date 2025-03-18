<?php

namespace App\Repositories\Post;

use App\Models\Post;
use App\Models\PostPlatform;
use App\Models\SocialAccount;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PostRepositoryImplementation implements PostRepositoryInterface
{
    public function getAll()
    {
        //
    }

    public function getAllPostPlatforms() {
        return PostPlatform::where('status', 'SUCCESS')->get();
    }

    public function getScheduledPosts()
    {
        $posts = Post::where('scheduled_time', '<=', Carbon::now())
            ->whereDoesntHave('postPlatforms', function ($query) {
                $query->where('status', 'SUCCESS');
            })
            ->get();
        return $posts;
    }

    public function getPostPlatforms(Post $post)
    {
        return PostPlatform::where('post_id', $post->id)->with('socialAccount')->get();
    }

    public function updatePostPlatform($postPlatform, $status, $postPlatformId = null)
    {
        $postPlatform->update([
            'post_platform_id' => $postPlatformId,
            'status' => $status,
            'posted_at' => Carbon::now(),
        ]);
    }

    public function getMyPosts($userId)
    {
        $posts =  Post::where("user_id", $userId)->get();

        foreach ($posts as $post) {
            $post->postPlatforms = PostPlatform::where('post_id', $post->id)->get();
        }

        return $posts;
    }

    public function create(array $data)
    {
        $userId = $data["user_id"];
        $content = $data["content"];
        $mediaUrls = $data["media_urls"];
        $scheduledTime = $data["scheduled_time"];
        $listPlatforms = $data["list_platforms"];

        DB::transaction(function () use ($userId, $content, $mediaUrls, $scheduledTime, $listPlatforms) {
            $post = Post::create([
                Post::ID => Str::uuid(),
                Post::USER_ID => $userId,
                Post::CONTENT => $content,
                Post::MEDIA_URLS => $mediaUrls,
                Post::SCHEDULED_TIME => $scheduledTime
            ]);
        
            $postPlatformsData = [];
            foreach ($listPlatforms as $platform) {
                $socialAccount = SocialAccount::where('user_id', $userId)
                    ->where('platform', $platform)
                    ->first();
        
                if ($socialAccount) {
                    $postPlatformsData[] = [
                        PostPlatform::ID => Str::uuid(),
                        PostPlatform::POST_ID => $post->id,
                        PostPlatform::PLATFORM => $platform,
                        PostPlatform::SOCIAL_ACCOUNT_ID => $socialAccount->id,
                        PostPlatform::CREATED_AT => now()
                    ];
                }
            }

            if (!empty($postPlatformsData)) {
                PostPlatform::insert($postPlatformsData);
            }
        });

        return;
    }

    public function getById($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return null;
        }
        $post->postPlatforms = PostPlatform::where('post_id', $id)->get();
        return $post;
    }

    public function update($id, array $data)
    {
        $userId = $data["user_id"];
        $content = $data["content"];
        $mediaUrls = $data["media_urls"];
        $scheduledTime = $data["scheduled_time"];
        $listPlatforms = $data["list_platforms"];

        DB::transaction(function () use ($id, $userId, $content, $mediaUrls, $scheduledTime, $listPlatforms) {
            $post = Post::findOrFail($id);
            $post->update([
                Post::USER_ID => $userId,
                Post::CONTENT => $content,
                Post::MEDIA_URLS => $mediaUrls,
                Post::SCHEDULED_TIME => $scheduledTime
            ]);
        
            PostPlatform::where('post_id', $id)->delete();
        
            $postPlatformsData = [];
            foreach ($listPlatforms as $platform) {
                $socialAccount = SocialAccount::where('user_id', $userId)
                    ->where('platform', $platform)
                    ->first();
        
                if ($socialAccount) {
                    $postPlatformsData[] = [
                        PostPlatform::ID => Str::uuid(),
                        PostPlatform::POST_ID => $id,
                        PostPlatform::PLATFORM => $platform,
                        PostPlatform::SOCIAL_ACCOUNT_ID => $socialAccount->id,
                        PostPlatform::CREATED_AT => now()
                    ];
                }
            }
        
            if (!empty($postPlatformsData)) {
                PostPlatform::insert($postPlatformsData);
            }
        });

        return;
    }

    public function delete($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
    }

    public function getDeletedPosts()
    {
        $result = [];
        $deletedPosts = Post::onlyTrashed()->get();
        foreach ($deletedPosts as $post) {
            $post->postPlatforms = PostPlatform::onlyTrashed()->where('post_id', $post->id);
            $result[] = $post;
        }
        return $result;
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $postPlatforms = PostPlatform::onlyTrashed()->where('post_id', $id)->get();
            $postPlatforms->each->restore();

            $post = Post::withTrashed()->findOrFail($id);
            $post->restore();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public function forceDelete($id)
    {
        $user = Post::withTrashed()->findOrFail($id);
        $user->forceDelete();
    }
}
