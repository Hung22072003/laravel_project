<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\Post\PostRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HTTPStatus;

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

            $tweetService = new TweetService($socialAccount->access_token, $socialAccount->access_token_secret);
            $mediaUrls = json_decode($post->media_urls, true);
            $result = $tweetService->store($post->content,  $mediaUrls);

            if ($result['httpCode'] == HTTPStatus::HTTP_CREATED) {
                $this->postRepository->updatePostPlatformStatus($postPlatform, Post::STATUSES['SUCCESS']);
                Log::info('Publish posts successfully');
            } else {
                $this->postRepository->updatePostPlatformStatus($postPlatform, Post::STATUSES['FAILED']);
                Log::error('TweetService failed response:', [
                    'httpCode' => $result['httpCode'],
                    'response' => json_encode($result['response'], JSON_PRETTY_PRINT)
                ]);
            }
        }
    }

    public function store(array $data)
    {
        return $this->postRepository->create($data);
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
