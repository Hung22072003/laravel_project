<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
class LinkedinController extends ControllerWithGuard
{
    public function postToLinkedIn(Request $request)
    {
        $accessToken = $request->input('access_token');
        $message = $request->input('message');
        $images = $request->file('images'); // Nhận danh sách ảnh từ form

        // var_dump($images);
        // Lấy User ID từ LinkedIn
        $userResponse = Http::withToken($accessToken)->get('https://api.linkedin.com/v2/userinfo');
        $userId = $userResponse->json()['sub'];

        // echo $images;
        $assetIds = [];

        // echo $userId;
        // Bước 1: Đăng ký và upload từng ảnh lên LinkedIn
        if($images) {
            // echo count($images);
            foreach ($images as $image) {
                // echo $image;
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
                // echo $uploadData;
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
                    ])->put($uploadUrl, file_get_contents($image->getPathname()));
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
        // Bước 2: Đăng bài viết kèm nhiều ảnh
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
        // $postPayload = json_encode($postPayload, JSON_INVALID_UTF8_IGNORE);
        // $postPayload = utf8ize($postPayload);
        // $postPayload = json_encode($postPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);

        // utf8EncodeDeep($postPayload);
        $postResponse = Http::withToken($accessToken)
            // ->withHeaders(['Content-Type' => 'application/json'])
            ->post("https://api.linkedin.com/v2/ugcPosts", $postPayload);

        return response()->json($postResponse->json());
    }

    function utf8EncodeDeep(&$input) {
        if (is_string($input)) {
            $input = mb_convert_encoding($input, 'UTF-8', 'UTF-8');
        } elseif (is_array($input)) {
            array_walk_recursive($input, 'utf8EncodeDeep');
        }
    }
}
