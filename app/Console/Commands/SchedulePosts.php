<?php

namespace App\Console\Commands;

use App\Services\PostService;
use Illuminate\Console\Command;
use App\Models\Post;
use App\Jobs\PublishPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;

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
     * The post service instance.
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

        Bus::batch(
            collect($posts)->map(fn($post) => new PublishPost($post))->toArray()
        )->dispatch();
        
        $this->info("Batch job dispatched for publishing " . count($posts) . " posts.");
    }
}
