<?php

namespace App\Services;

use App\Repositories\Interaction\InteractionRepositoryInterface;

class InteractionService
{
    private $interactionRepository;

    public function __construct(InteractionRepositoryInterface $interactionRepository)
    {
        $this->interactionRepository = $interactionRepository;
    }

    public function createOrUpdateInteraction($postPlatformId, $data) {
        $this->interactionRepository->createOrUpdateInteraction($postPlatformId, $data);
    }
}