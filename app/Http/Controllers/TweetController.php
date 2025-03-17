<?php

namespace App\Http\Controllers;

use App\Http\Requests\TweetStoreRequest;
use App\Services\TweetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TweetController extends ControllerWithGuard
{
    private $tweetService;

    public function __construct(TweetService $tweetService)
    {
        parent::__construct(); 
        $this->tweetService = $tweetService;
    }

    public function store(TweetStoreRequest $request)
    {    
        $result = $this->tweetService->store($request->message, $request->mediaPaths ? $request->mediaPaths : []);
        return response()->json($result['response'], $result['httpCode']);
    }

    public function destroy($id)
    {
        $result = $this->tweetService->destroy($id);
        return response()->json($result['response'], $result['httpCode']);
    }

    public function show($id)
    {
        $result = $this->tweetService->show($id);
        return response()->json($result['response'], $result['httpCode']);
    }

    public function myTweets()
    {
        $result = $this->tweetService->myTweets();
        return response()->json($result['response'], $result['httpCode']);
    }

    public function tweetInteractions($id)
    {
        $result = $this->tweetService->tweetInteractions($id);
        return response()->json($result['response'], $result['httpCode']);
    }
}
