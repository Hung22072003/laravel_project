<?php

namespace App\Services;

use App\Repositories\Interaction\InteractionRepositoryInterface;
use Illuminate\Support\Facades\Log;

class InteractionService
{
    private $interactionRepository;

    public function __construct(InteractionRepositoryInterface $interactionRepository)
    {
        $this->interactionRepository = $interactionRepository;
    }

    public function createOrUpdateInteraction($postPlatformId, $data) {
        Log::info('Vào được Service đây rồi');
        $this->interactionRepository->createOrUpdateInteraction($postPlatformId, $data);
    }
}