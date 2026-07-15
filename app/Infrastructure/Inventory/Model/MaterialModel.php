<?php

namespace App\Infrastructure\Inventory\Model;

use Illuminate\Database\Eloquent\Model;

final class MaterialModel extends Model
{
    protected $table = 'materials';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'sku', 'name', 'unit', 'category'];
}
