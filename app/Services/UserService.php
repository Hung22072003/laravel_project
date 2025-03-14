<?php

namespace App\Services;

use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserService
{
    private $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    public function index()
    {
        return $this->userRepository->getAll();
    }

    public function store(String $full_name, String $email, String $password)
    {
        return $this->userRepository->create([
            'full_name' => $full_name,
            'email' => $email,
            'hashedPassword' => Hash::make($password),
        ]);
    }

    public function show($id)
    {
        return $this->userRepository->getById($id);
    }

    public function update($id, array $data)
    {
        return $this->userRepository->update($id, [
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'hashedPassword' => Hash::make( $data['password']),
        ]);
    }

    public function destroy($id)
    {
        return $this->userRepository->delete($id);
    }

    public function getDeletedUsers()
    {
        return $this->userRepository->getDeletedUsers();
    }

    public function restore($id)
    {
        return $this->userRepository->restore($id);
    }

    public function forceDelete($id)
    {
        return $this->userRepository->forceDelete($id);
    }

    public function changePassword($id, $password)
    {
        return $this->userRepository->changePassword($id, Hash::make($password));
    }
}
