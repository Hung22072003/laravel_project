<?php

namespace App\Jobs;

use App\Models\Post;
use App\Models\SocialAccount;
use App\Services\LinkedinService;
use App\Services\PostService;
use App\Services\TweetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Batchable; 
use Illuminate\Queue\SerializesModels;

class PublishPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $data;
    public $platform;
    public $id;
    public function __construct(array $data, $platform, &$id)
    {
        $this->data = $data;
        $this->platform = $platform;
        $this->id = $id;
    }

    public function handle(PostService $postService, LinkedinService $linkedinService, TweetService $tweetService)
    {
        // $listId = [];

        // foreach ($this->data['list_platforms'] as $platform) {
            switch ($this->platform) {
                case 'LINKEDIN':
                    // $listId['LINKEDIN'] = $linkedinService->postToLinkedIn(
                    //     $this->data['content'],
                    //     $this->data['media_urls'] ?? []
                    // );
                    $this->id =  $linkedinService->postToLinkedIn(
                        $this->data['content'],
                        $this->data['media_urls'] ?? []
                    );
                    // echo $id;
                    echo $this->id;
                    return $this->id;
                    break;

                case 'TWITTER':
                    $account = SocialAccount::where('user_id', $this->data['user_id'])
                        ->where('platform', 'TWITTER')
                        ->first();

                    if ($account) {
                        $tweetService = new TweetService(
                            $account->access_token,
                            $account->access_token_secret
                        );

                        // $listId['TWITTER'] = $tweetService->store(
                        //     $this->data['content'],
                        //     $this->data['media_urls'] ?? []
                        // );

                        return $tweetService->store(
                            $this->data['content'],
                            $this->data['media_urls'] ?? []
                        );
                    }
                    break;
            }
        // }

        // $this->data['listId'] = $listId;

        // $postService->store($this->data);
    }
}
