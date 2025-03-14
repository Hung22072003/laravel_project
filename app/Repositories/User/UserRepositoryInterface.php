<?php

namespace App\Repositories\User;

use App\Repositories\Interfaces\BaseRepositoryInterface;

interface UserRepositoryInterface extends BaseRepositoryInterface {
    public function changePassword($id, String $hashedPassword);
    public function getDeletedUsers();
    public function restore($id);
    public function forceDelete($id);
}
