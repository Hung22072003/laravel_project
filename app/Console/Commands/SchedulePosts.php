<?php

namespace App\Console\Commands;

use App\Services\PostService;
use Illuminate\Console\Command;
use App\Models\Post;
use App\Jobs\PublishPost;
use Carbon\Carbon;

class SchedulePosts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'posts:publish';

    /**
     * The console command description.
     */
    protected $description = 'Publish scheduled posts to platforms';

    /**
     * Execute the console command.
     */
    protected $postService;

    public function __construct(PostService $postService)
    {
        parent::__construct();
        $this->postService = $postService;
    }

    public function handle()
    {
        $posts = $this->postService->getScheduledPosts();

        foreach ($posts as $post) {
            dispatch(new PublishPost($post));
            $this->info("Post {$post->id} dispatched for publishing.");
        }
    }
}
