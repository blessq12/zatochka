<?php

namespace App\Models;

use App\Infrastructure\Persistence\Eloquent\Models\Identity\UserModel;

/**
 * Laravel-алиас для auth/factory. Реализация — UserModel (Infrastructure).
 */
class User extends UserModel
{
}
