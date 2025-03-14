<?php

namespace App\Repositories\SocialAccount;

use App\Models\SocialAccount;

class SocialAccountRepositoryImplementation implements SocialAccountRepositoryInterface
{
    public function createOrUpdate($user_id, $access_token, $platform)
    {
        $socialAccount = SocialAccount::updateOrCreate(
            [
                'user_id' => $user_id,
                'platform' => $platform
            ],
            [
                'social_user_id' => $access_token['user_id'],
                'screen_name' => $access_token['screen_name'],
                'access_token' => $access_token['oauth_token'],
                'access_token_secret' => $access_token['oauth_token_secret'],
                'platform' => $platform
            ]
        );
        return $socialAccount;
    }

    public function getBySocialUserId($socialUserId, $platform)
    {
        $socialAccount = SocialAccount::where('social_user_id', $socialUserId)
            ->where('platform', $platform)
            ->first();
        return $socialAccount;
    }

    public function getByUserPlatform($userId, $platform)
    {
        $socialAccount = SocialAccount::where('user_id', $userId)
            ->where('platform', $platform)
            ->first(['id', 'user_id', 'social_user_id', 'screen_name', 'platform']);
        return $socialAccount;
    }

    public function getById($id)
    {
        $socialAccount = SocialAccount::find($id);
        return $socialAccount;
    }

    public function delete($id)
    {
        $socialAccount = SocialAccount::findOrFail($id);
        $socialAccount->delete();
    }

    public function getDeletedSocialAccounts()
    {
        return SocialAccount::onlyTrashed()->get();
    }

    public function restore($id)
    {
        $socialAccount = SocialAccount::withTrashed()->findOrFail($id);
        $socialAccount->restore();
    }

    public function forceDelete($id)
    {
        $socialAccount = SocialAccount::withTrashed()->findOrFail($id);
        $socialAccount->forceDelete();
    }

    public function getMySocialAccounts($userId)
    {
        $socialAccounts =  SocialAccount::where("user_id", $userId)->get();
        return $socialAccounts;
    }
}
