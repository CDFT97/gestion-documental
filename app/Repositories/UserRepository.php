<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

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
}
