<?php

namespace App\Jobs;

// use App\Models\Post;
// use App\Models\PostPlatform;
// use App\Models\SocialAccount;
// use App\Services\LinkedinService;
// use App\Services\PostService;
// use App\Services\TweetService;
// use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Foundation\Bus\Dispatchable;
// use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Bus\Batchable;
// use Illuminate\Queue\SerializesModels;

// class PublishPost implements ShouldQueue
// {
//     use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

//     public $post;
//     public $platform;
//     public function __construct(Post $post, $platform)
//     {
//         $this->post = $post;
//         $this->platform = $platform;
//     }

//     public function handle(PostService $postService, LinkedinService $linkedinService, TweetService $tweetService)
//     {
//         // $listId = [];
//         $id = null;

//         $socialAccount = SocialAccount::where('user_id', $this->post['user_id'])
//         ->where('platform', $this->platform)
//         ->first();
//         // foreach ($this->data['list_platforms'] as $platform) {
//             switch ($this->platform) {
//                 case 'LINKEDIN':
//                     // $listId['LINKEDIN'] = $linkedinService->postToLinkedIn(
//                     //     $this->data['content'],
//                     //     $this->data['media_urls'] ?? []
//                     // );
//                     $id =  $linkedinService->postToLinkedIn(
//                         $this->post['content'],
//                         $this->post['media_urls'] ?? []
//                     );

//                     break;

//                 case 'TWITTER':
//                     if ($socialAccount) {
//                         $tweetService = new TweetService(
//                             $socialAccount->access_token,
//                             $socialAccount->access_token_secret
//                         );

//                         // $listId['TWITTER'] = $tweetService->store(
//                         //     $this->data['content'],
//                         //     $this->data['media_urls'] ?? []
//                         // );

//                         return $tweetService->store(
//                             $this->post['content'],
//                             $this->post['media_urls'] ?? []
//                         );
//                     }
//                     break;
//             }
//         // }

//         // var_dump($listId);
//         // $this->data["listId"] = $listId;
//         // $postService->store($this->data);
//         if($id != null) {
//             PostPlatform::insert([
//                 PostPlatform::ID => $id,
//                 PostPlatform::POST_ID => $this->post['id'],
//                 PostPlatform::PLATFORM => $this->platform,
//                 PostPlatform::SOCIAL_ACCOUNT_ID => $socialAccount->id,
//                 PostPlatform::CREATED_AT => now(),
//                 PostPlatform::STATUS => $this->post['scheduled_time'] ? 'PENDING' : 'SUCCESS'
//             ]);
//         }
        
//     }
    use App\Models\Post;
    use App\Services\PostService;
    use Illuminate\Bus\Queueable;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Bus\Dispatchable;
    use Illuminate\Queue\InteractsWithQueue;
    use Illuminate\Bus\Batchable; 
    use Illuminate\Queue\SerializesModels;

    class PublishPost implements ShouldQueue
    {
        use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

        public $post;

        public function __construct(Post $post)
        {
            $this->post = $post;
        }

        public function handle(PostService $postService)
        {
            $postService->publish($this->post);
        }
}

