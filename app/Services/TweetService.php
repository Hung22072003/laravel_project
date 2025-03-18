<?php

namespace App\Services;

use Abraham\TwitterOAuth\TwitterOAuth;

class TweetService
{
    private $client;

    public function __construct($access_token='', $access_secret='')
    {
        $consumer_key = env('TWITTER_CONSUMER_KEY', '');
        $consumer_secret = env('TWITTER_CONSUMER_SECRET', '');
        $this->client = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_secret);
    }

    public function uploadMedias(array $mediaPaths): array
    {
        $mediaIds = [];

        foreach ($mediaPaths as $path) {
            $uploadedMedia = $this->client->upload("media/upload", ["media" => $path]);
            if (isset($uploadedMedia->media_id_string)) {
                $mediaIds[] = $uploadedMedia->media_id_string;
            }
        }

        return $mediaIds;
    }
    
    public function store(string $message, array $mediaPaths = [])
    {
        $mediaIds = $this->uploadMedias($mediaPaths);

        $parameters = ["text" => $message];
        if (!empty($mediaIds)) {
            $parameters["media"] = ["media_ids" => $mediaIds];
        }

        $response = $this->client->post("tweets", $parameters);

        var_dump($response);
        return [
            'httpCode' => $this->client->getLastHttpCode(),
            'response' => $response
        ];

        // return $response["data"]->id;
    }

    public function destroy($id)
    {
        $response = $this->client->delete("tweets/{$id}");
        return [
            'httpCode' => $this->client->getLastHttpCode(),
            'response' => $response
        ];
    }

    public function show($id)
    {
        $response = $this->client->get("tweets/{$id}");
        return [
            'httpCode' => $this->client->getLastHttpCode(),
            'response' => $response
        ];
    }

    public function myTweets()
    {
        $user = $this->client->get("users/me");

        if (!isset($user->data->id)) {
            return [
                'httpCode' => 400,
                'response' => "Failed to fetch user details: " . json_encode($user)
            ];
        }

        $userId = $user->data->id;
        $response = $this->client->get("users/{$userId}/tweets");

        return [
            'httpCode' => $this->client->getLastHttpCode(),
            'response' => $response
        ];
    }

    public function tweetInteractions($tweetId)
    {
        $likes = $this->client->get("tweets/{$tweetId}/liking_users");
        $retweets = $this->client->get("tweets/{$tweetId}/retweeted_by");
        $replies = $this->client->get("tweets/search/recent", [
            'query' => "conversation_id:{$tweetId}"
        ]);

        return [
            'httpCode' => $this->client->getLastHttpCode(),
            'response' => [
                "likes" => $likes,
                "retweets" => $retweets,
                "replies" => $replies
            ]
        ];
    }
}
