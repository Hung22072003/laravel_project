<?php

namespace App\Http\Controllers;

use App\Http\Requests\SocialAccountShowRequest;
use App\Services\SocialAccountService;
use App\Traits\APIResponse;
use Illuminate\Http\Request;

class SocialAccountController extends ControllerWithGuard
{
    use APIResponse;

    private $socialAccountService;

    public function __construct(SocialAccountService $socialAccountService)
    {
        parent::__construct();
        $this->socialAccountService = $socialAccountService;
    }

    public function showByUserPlatform(SocialAccountShowRequest $request)
    {
        $platform = $request->query('platform');
        $account = $this->socialAccountService->showByUserPlatform($request->user()->id, $platform);

        if (!$account) {
            return $this->responseError('Social account not found', 404);
        }

        return $this->responseSuccessWithData($account);
    }

    public function mySocialAccounts(Request $request)
    {
        $result = $this->socialAccountService->showMySocialAccounts($request->user()->id);
        return $this->responseSuccessWithData($result);
    }

    /**
     * Soft deletes a social account by their ID.
     *
     * @param int $id The ID of the social account to be soft deleted.
     * @return \Illuminate\Http\JsonResponse A success response indicating the social account has been soft deleted.
     */
    public function destroy($id)
    {
        $account = $this->socialAccountService->show($id);

        if (!$account) {
            return $this->responseError('Social account not found', 404);
        }
        $this->socialAccountService->destroy($id);
        return $this->responseSuccess('Social account soft deleted successfully!');
    }

    /**
     * Get all the deleted social accounts.
     * @return \Illuminate\Http\JsonResponse A success response with data of the deleted social accounts.
     */
    public function getDeletedSocialAccounts()
    {
        $result = $this->socialAccountService->getDeletedSocialAccounts();
        return $this->responseSuccessWithData($result);
    }

    /**
     * Restore a deleted social account by their ID.
     *
     * @param int $id The ID of the deleted social account.
     * @return \Illuminate\Http\JsonResponse A success response indicating the social account has been restored.
     */
    public function restore($id)
    {
        $this->socialAccountService->restore($id);
        return $this->responseSuccess('Deleted social account restored successfully!');
    }

    /**
     * Delete social account permanently by their ID.
     *
     * @param int $id The ID of the social account.
     * @return \Illuminate\Http\JsonResponse A success response indicating the social account has been deleted permanently.
     */
    public function forceDelete($id)
    {
        $account = $this->socialAccountService->show($id);

        if (!$account) {
            return $this->responseError('Social account not found', 404);
        }
        $this->socialAccountService->forceDelete($id);
        return $this->responseSuccess('Social account permanently deleted!');
    }
}
