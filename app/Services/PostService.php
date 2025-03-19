<?php

namespace App\Services;

use App\Jobs\AsyncPost;
use App\Models\Post;
use App\Repositories\Post\PostRepositoryInterface;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HTTPStatus;
use Illuminate\Support\Str;

class PostService
{
    private $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function index()
    {
        // 
    }

    public function getAllPostPlatforms() {
        return $this->postRepository->getAllPostPlatforms();
    }

    public function getScheduledPosts()
    {
        return $this->postRepository->getScheduledPosts();
    }

    public function publish(Post $post)
    {
        $postPlatforms = $this->postRepository->getPostPlatforms($post);

        foreach ($postPlatforms as $postPlatform) {
            $socialAccount = $postPlatform->socialAccount;
            Log::info('socialAccount:', $socialAccount->toArray());
            Log::info('Access Token: ' . $socialAccount->access_token);
            Log::info('Access Token Secret: ' . $socialAccount->access_token_secret);

            $mediaUrls = $post->media_urls;
            switch ($socialAccount->platform) {
                case "LINKEDIN": {
                        $linkedinService = new LinkedinService();
                        $result = $linkedinService->postToLinkedin($post->content, $mediaUrls ?? [], $socialAccount->access_token);
                        break;
                    }
                case "TWITTER": {
                        $tweetService = new TweetService($socialAccount->access_token, $socialAccount->access_token_secret);
                        $result = $tweetService->store($post->content, $mediaUrls ?? []);
                        break;
                    }
            }

            if ($result['httpCode'] == HTTPStatus::HTTP_CREATED) {
                $this->postRepository->updatePostPlatform($postPlatform, Post::STATUSES['SUCCESS'], $result['response']);
                Log::info('Publish posts successfully');
            } else {
                $this->postRepository->updatePostPlatform($postPlatform, Post::STATUSES['FAILED'], null);
                Log::error('Publish failed response:', [
                    'httpCode' => $result['httpCode'],
                    'response' => json_encode($result['response'], JSON_PRETTY_PRINT)
                ]);
            }
        }
    }

    public function store(array $data)
    {
        if (!empty($data['media_urls'])) {
            $data['media_urls'] = array_map(function ($media) {
                return Cloudinary::upload($media->getRealPath(), ['folder' => 'smart_post'])
                    ->getSecurePath();
            }, $data['media_urls']);
        }

        if (isset($data["scheduled_time"])) {
            $this->postRepository->create($data);
        } else {
            $post = Post::create([
                Post::ID => Str::uuid(),
                Post::USER_ID => $data["user_id"],
                Post::CONTENT => $data["content"],
                Post::MEDIA_URLS =>  $data['media_urls'],
                Post::SCHEDULED_TIME =>  $data["scheduled_time"]
            ]);
            foreach ($data["list_platforms"] as $platform) {
                AsyncPost::dispatch($post, $platform);
            }
        }
    }

    public function show($id)
    {
        return $this->postRepository->getById($id);
    }

    public function myPosts($userId)
    {
        return $this->postRepository->getMyPosts($userId);
    }

    public function update($id, array $data)
    {
        return $this->postRepository->update($id, $data);
    }

    public function destroy($id)
    {
        return $this->postRepository->delete($id);
    }

    public function getDeletedPosts()
    {
        return $this->postRepository->getDeletedPosts();
    }

    public function restore($id)
    {
        return $this->postRepository->restore($id);
    }

    public function forceDelete($id)
    {
        return $this->postRepository->forceDelete($id);
    }
}
