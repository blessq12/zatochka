<?php

namespace App\Infrastructure\Order\Model;

use Illuminate\Database\Eloquent\Model;

final class DocumentTemplateModel extends Model
{
    protected $table = 'document_templates';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'kind',
        'name',
        'body_html',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
