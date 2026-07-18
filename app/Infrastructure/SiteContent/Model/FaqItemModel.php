<?php

namespace App\Infrastructure\SiteContent\Model;

use Illuminate\Database\Eloquent\Model;

final class FaqItemModel extends Model
{
    protected $table = 'site_faq_items';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'question',
        'answer_lines',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'answer_lines' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
