<?php

namespace App\Services;

use App\Services\SocialAccountService;
use App\Services\PostService;
use Illuminate\Support\Facades\Http;
use App\Traits\APIResponse;

class LinkedinService
{
    private $socialAccountService;
    private $postService;
    use APIResponse;
    public function __construct()
    {
        // $this->socialAccountService = $socialAccountService;
        // $this->postService = $postService;
    }
    public function uploadMedias(string $userId, string $accessToken, array $images)
    {
        $assetIds = [];

        // Bước 1: Đăng ký và upload từng ảnh lên LinkedIn
        if ($images) {
            foreach ($images as $image) {
                $uploadResponse = Http::withToken($accessToken)->post("https://api.linkedin.com/v2/assets?action=registerUpload", [
                    "registerUploadRequest" => [
                        "recipes" => ["urn:li:digitalmediaRecipe:feedshare-image"],
                        "owner" => "urn:li:person:$userId",
                        "serviceRelationships" => [
                            [
                                "relationshipType" => "OWNER",
                                "identifier" => "urn:li:userGeneratedContent",
                            ],
                        ],
                    ],
                ]);

                $uploadData = $uploadResponse->json();
                $assetId = $uploadData['value']['asset'] ?? null;

                if (!$assetId) {
                    return response()->json(['error' => 'Failed to get asset ID'], 400);
                }

                $uploadUrl = $uploadData['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;


                if ($uploadUrl) {
                    // Upload ảnh lên LinkedIn
                    Http::withHeaders([
                        'Authorization' => "Bearer $accessToken",
                        'Content-Type' => 'application/octet-stream',
                    ])->withBody(file_get_contents($image), 'application/octet-stream')
                        ->put($uploadUrl);
                }

                // Lưu lại asset ID cho bài viết
                $assetIds[] = [
                    "status" => "READY",
                    "media" => $assetId,
                    "description" => ["text" => "Image"],
                    "title" => ["text" => "Uploaded Image"],
                ];
            }
        }
        return $assetIds;
    }

    public function postToLinkedin(string $message, array $images, $accessToken)
    {
        $userResponse = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/userinfo');
        $userId = $userResponse->json()['sub'];
        $assetIds = $this->uploadMedias($userId, $accessToken, $images);

        $postPayload = [
            "author" => "urn:li:person:$userId",
            "lifecycleState" => "PUBLISHED",
            "specificContent" => [
                "com.linkedin.ugc.ShareContent" => [
                    "shareCommentary" => [
                        "text" => $message,
                    ],
                    "shareMediaCategory" => $assetIds ? "IMAGE" : "NONE",
                    "media" => $assetIds ? $assetIds : [],
                ],
            ],
            "visibility" => [
                "com.linkedin.ugc.MemberNetworkVisibility" => "PUBLIC",
            ],
        ];

        $postResponse = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("https://api.linkedin.com/v2/ugcPosts", $postPayload);
        $response = $postResponse->json();

        if($postResponse->getStatusCode() == 201) {
            return [
                'httpCode' => $postResponse->getStatusCode(),
                'response' =>  $response["id"]
            ];
        } else {
            return [
                'httpCode' => $postResponse->getStatusCode(),
                'response' =>  null
            ];
        }
    }
}
