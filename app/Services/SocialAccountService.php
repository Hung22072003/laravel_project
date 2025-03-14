<?php

namespace App\Services;

use Abraham\TwitterOAuth\Request;
use App\Repositories\SocialAccount\SocialAccountRepositoryInterface;

class SocialAccountService
{
    protected $socialAccountRepository;

    public function __construct(SocialAccountRepositoryInterface $socialAccountRepository)
    {
        $this->socialAccountRepository = $socialAccountRepository;
    } 

    public function store($user_id, $access_token, $platform): mixed
    {
        return $this->socialAccountRepository->createOrUpdate($user_id, $access_token, $platform);
    }

    public function show($id)
    {
        return $this->socialAccountRepository->getById($id);
    }

    public function showBySocialUserId($socialUserId, $platform)
    {
        return $this->socialAccountRepository->getBySocialUserId($socialUserId, $platform);
    }

    public function showByUserPlatform($userId, $platform)
    {
        return $this->socialAccountRepository->getByUserPlatform($userId, $platform);
    }

    public function showMySocialAccounts($userId)
    {
        return $this->socialAccountRepository->getMySocialAccounts($userId);
    }

    public function destroy($id)
    {
        return $this->socialAccountRepository->delete($id);
    }

    public function getDeletedSocialAccounts()
    {
        return $this->socialAccountRepository->getDeletedSocialAccounts();
    }

    public function restore($id)
    {
        return $this->socialAccountRepository->restore($id);
    }

    public function forceDelete($id)
    {
        return $this->socialAccountRepository->forceDelete($id);
    }

}
