<?php

namespace App\Models;

use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;

/**
 * Laravel-алиас для auth/factory. Реализация — UserModel (Infrastructure).
 */
class User extends UserModel
{
}
