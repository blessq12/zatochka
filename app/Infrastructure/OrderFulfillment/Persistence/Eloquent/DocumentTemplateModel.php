<?php

namespace App\Infrastructure\OrderFulfillment\Persistence\Eloquent;

use App\Infrastructure\Identity\Persistence\Eloquent\UserModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplateModel extends Model
{
    protected $table = 'document_templates';

    protected $fillable = [
        'type',
        'body',
        'updated_by_user_id',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'updated_by_user_id');
    }
}
