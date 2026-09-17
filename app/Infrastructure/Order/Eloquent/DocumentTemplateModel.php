<?php

namespace App\Infrastructure\Order\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class DocumentTemplateModel extends Model
{
    protected $table = 'document_templates';

    protected $fillable = [
        'type',
        'body',
        'updated_by_identity_id',
    ];
}
