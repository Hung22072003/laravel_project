<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostStoreRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Jobs\PublishPost;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Illuminate\Support\Facades\Log;

class PostController extends ControllerWithGuard
{
    use APIResponse;

    private $postService;
    const LIST_PLATFORMS = 'list_platforms';

    public function __construct(PostService $postService)
    {
        parent::__construct();
        $this->postService = $postService;
    }

    public function index()
    {
        //
    }
    public function publish(PostStoreRequest $request)
    {
        $data = [
            'user_id' => $request->user()->id,
            'content' => $request->content,
            'media_urls' => $request->mediaUrls,
            'scheduled_time' => $request->scheduledTime,
            'list_platforms' => $request->listPlatforms
        ];
        
        
        $listId = [];
        foreach ($data['list_platforms'] as $platform) {
            $id = null;
            var_dump(PublishPost::dispatch($data, $platform, $id));
            // echo $id;
            $listId[$platform] = $id;
        }

        $data['listId'] = $listId;

        // $this->postService->store($data);
        // PublishPost::dispatch($data);    
        // var_dump($listId);

        return $this->responseSuccess('Post created successfully!');
    }

    public function store(PostStoreRequest $request)
    {
        $data = [
            Post::USER_ID=> $request->user()->id,
            Post::CONTENT => $request->content,
            Post::MEDIA_URLS => $request->mediaUrls,
            Post::SCHEDULED_TIME => $request->scheduledTime,
            self::LIST_PLATFORMS => $request->listPlatforms
        ];

        $this->postService->store($data);

        return $this->responseSuccess('Post created successfully!');
    }

    public function myPosts(Request $request)
    {
        $userId = $request->user()->id;
        $result = $this->postService->myPosts($userId);
        return $this->responseSuccessWithData($result);
    }

    public function show($id)
    {
        $post = $this->postService->show($id);

        if (!$post) {
            return $this->responseError('Post not found', 404);
        }

        return $this->responseSuccessWithData($post);
    }

    public function update($id, PostUpdateRequest $request)
    {
        $post = $this->postService->show($id);

        if (!$post) {
            return $this->responseError('Post not found', 404);
        }

        $data = [
            Post::USER_ID=> $request->user()->id,
            Post::CONTENT => $request->content,
            Post::MEDIA_URLS => $request->mediaUrls,
            Post::SCHEDULED_TIME => $request->scheduledTime,
            self::LIST_PLATFORMS => $request->listPlatforms
        ];

         $this->postService->update($id, $data);

        return $this->responseSuccess('Post updated successfully!');
    }

    /**
     * Soft deletes a post by their ID.
     *
     * @param int $id The ID of the post to be soft deleted.
     * @return \Illuminate\Http\JsonResponse A success response indicating the post has been soft deleted.
     */
    public function destroy($id)
    {
        $post = $this->postService->show($id);

        if (!$post) {
            return $this->responseError('Post not found', 404);
        }

        $this->postService->destroy($id);
        return $this->responseSuccess('Post soft deleted successfully!');
    }

    /**
     * Get all the deleted posts.
     * @return \Illuminate\Http\JsonResponse A success response with data of the deleted posts.
     */
    public function getDeletedPosts()
    {
        $result = $this->postService->getDeletedPosts();
        return $this->responseSuccessWithData($result);
    }

    /**
     * Restore a deleted post by their ID.
     *
     * @param int $id The ID of the deleted post.
     * @return \Illuminate\Http\JsonResponse A success response indicating the post has been restored.
     */
    public function restore($id)
    {
        $this->postService->restore($id);
        return $this->responseSuccess('Deleted post restored successfully!');
    }

    /**
     * Delete post permanently by their ID.
     *
     * @param int $id The ID of the post.
     * @return \Illuminate\Http\JsonResponse A success response indicating the post has been deleted permanently.
     */
    public function forceDelete($id)
    {
        $post = $this->postService->show($id);

        if (!$post) {
            return $this->responseError('Post not found', 404);
        }

        $this->postService->forceDelete($id);
        return $this->responseSuccess('Post permanently deleted!');
    }
}
