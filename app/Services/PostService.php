<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\Post\PostRepositoryInterface;

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
            $socialAccount = $postPlatform->social_account;
            $tweetService = new TweetService($socialAccount->access_token, $socialAccount->access_token_secret);

            $result = $tweetService->store($post->content, $post->media_urls);

            if ($result['httpCode'] == 200) {
                $this->postRepository->updatePostPlatformStatus($postPlatform, 'SUCCESS');
            } else {
                $this->postRepository->updatePostPlatformStatus($postPlatform, 'FAILED');
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
