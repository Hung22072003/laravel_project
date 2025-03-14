<?php

namespace App\Repositories\Post;

use App\Models\Post;
use App\Models\PostPlatform;
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
        return $post->postPlatforms;
    }

    public function updatePostPlatformStatus($postPlatform, $status)
    {
        $postPlatform->update([
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

        DB::beginTransaction();
        try {
            $postId = Str::uuid();
            DB::table('posts')->insert([
                'id' => $postId,
                'user_id' => $userId,
                'content' => $content,
                'media_urls' => json_encode($mediaUrls),
                'scheduled_time' => $scheduledTime,
                'created_at' => now(),
            ]);

            foreach ($listPlatforms as $platform) {
                $socialAccount = DB::table('social_accounts')
                    ->where('user_id', $userId)
                    ->where('platform', $platform)
                    ->first();

                if ($socialAccount) {
                    DB::table('post_platforms')->insert([
                        'id' => Str::uuid(),
                        'post_id' => $postId,
                        'platform' => $platform,
                        'social_account_id' => $socialAccount->id,
                        'created_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return ['success' => true, 'message' => 'Post created successfully!'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
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

        DB::beginTransaction();
        try {
            DB::table('posts')->where('id', $id)->update([
                'user_id' => $userId,
                'content' => $content,
                'media_urls' => json_encode($mediaUrls),
                'scheduled_time' => $scheduledTime,
            ]);

            DB::table('post_platforms')->where('post_id', $id)->delete();

            foreach ($listPlatforms as $platform) {
                $socialAccount = DB::table('social_accounts')
                    ->where('user_id', $userId)
                    ->where('platform', $platform)
                    ->first();

                if ($socialAccount) {
                    DB::table('post_platforms')->insert([
                        'id' => Str::uuid(),
                        'post_id' => $id,
                        'platform' => $platform,
                        'social_account_id' => $socialAccount->id
                    ]);
                }
            }

            DB::commit();
            return ['success' => true, 'message' => 'Post updated successfully!'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
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
