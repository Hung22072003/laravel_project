<?php

namespace App\Repositories\Interaction;


interface InteractionRepositoryInterface
{
    public function createOrUpdateInteraction($postPlatformId, $data);
}
