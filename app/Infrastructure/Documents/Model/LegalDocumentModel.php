<?php

namespace App\Infrastructure\Documents\Model;

use Illuminate\Database\Eloquent\Model;

final class LegalDocumentModel extends Model
{
    protected $table = 'legal_documents';

    protected $primaryKey = 'type';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'type',
        'title',
        'body_html',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }
}
