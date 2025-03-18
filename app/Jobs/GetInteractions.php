<?php

namespace App\Jobs;

use App\Models\PostPlatform;
use App\Services\InteractionService;
use App\Services\TweetService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetInteractions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;
    public $postPlatform;
    /**
     * Create a new job instance.
     */
    public function __construct(PostPlatform $postPlatform)
    {
        $this->postPlatform = $postPlatform;
    }

    /**
     * Execute the job.
     */
    public function handle(TweetService $tweetService, InteractionService $interactionService)
    {    
        $result = null;
        switch($this->postPlatform["platform"]) {
            case "TWITTER": {
                $result = $tweetService->tweetInteractions($this->postPlatform["post_platform_id"]);
                Log::info('result', $result);
            }
        }

        if($result != null) {
            $interactionService->createOrUpdateInteraction($this->postPlatform["post_platform_id"], $result);
        }
    }
}
