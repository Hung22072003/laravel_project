<?php

namespace App\Repositories\SocialAccount;

use App\Repositories\Interfaces\BaseRepositoryInterface;


interface SocialAccountRepositoryInterface {
    public function createOrUpdate($user_id, $access_token, $platform);
    public function getById($id);
    public function delete($id);
    public function getMySocialAccounts($userId);
    public function getDeletedSocialAccounts();
    public function restore($id);
    public function forceDelete($id);
    public function getBySocialUserId($socialUserId, $platform);
    public function getByUserPlatform($userId, $platform);
}
