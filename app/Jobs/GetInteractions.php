<?php

namespace App\Jobs;

use App\Models\PostPlatform;
use App\Services\InteractionService;
use App\Services\SocialAccountService;
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
    public function handle(SocialAccountService $socialAccountService, InteractionService $interactionService)
    {    
        $result = null;
        $socialAccount = $socialAccountService->show($this->postPlatform["social_account_id"]);
        switch($this->postPlatform["platform"]) {
            case "TWITTER": {
                $tweetService = new TweetService($socialAccount->access_token, $socialAccount->access_token_secret);
                $result = $tweetService->tweetInteractions($this->postPlatform["post_platform_id"]);
                // Log::info('result'. $result);
            }
        }

        if($result != null) {
            Log::info('Vào được đây rồi');
            $interactionService->createOrUpdateInteraction($this->postPlatform["id"], $result);
        }
    }
}
