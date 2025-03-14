<?php

namespace App\Http\Controllers;

use App\Http\Requests\LinkedinPostRequest;
use App\Services\LinkedinService;

class LinkedinController extends ControllerWithGuard
{
    private $linkedinService;
    public function __construct(LinkedinService $linkedinService)
    {
        parent::__construct(); 
        $this->linkedinService = $linkedinService;
    }

    public function postToLinkedIn(LinkedinPostRequest $request)
    {
        return $this->linkedinService->postToLinkedin($request->message ? $request->message : "", $request->images ? $request->images : []);
    }
}
