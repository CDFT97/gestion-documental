<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserRepository extends BaseRepository
{
  public function __construct(User $user)
  {
    parent::__construct($user);
  }

  public function findOrCreate(array $userData)
  {
    $user = $this->model->where('email', $userData['email'])->first();
    if (!$user) {
      $user = $this->model->create([
        'email' => $userData['email'],
        'name' => $userData['name'],
        'last_name' => $userData['last_name'],
        'password' => bcrypt(Str::random(32)),
        'garden_permission' => 1,
        'role_id' => Role::ROLE_CUSTOMER,
      ]);
    }

    return $user;
  }

  public function updateProfile(User $user, array $data): User
  {
    $user->update([
      'name' => $data['name'],
      'email' => $data['email'],
    ]);

    return $user->fresh();
  }

  public function verifyCurrentPassword(User $user, string $currentPassword): bool
  {
    return Hash::check($currentPassword, $user->password);
  }

  public function updatePassword(User $user, string $newPassword): bool
  {
    return $user->update([
      'password' => Hash::make($newPassword)
    ]);
  }

  public function getUserTables(User $user): array
  {
    return $tablesCount = $user->tables()->count() ?? 0;
  }
}
