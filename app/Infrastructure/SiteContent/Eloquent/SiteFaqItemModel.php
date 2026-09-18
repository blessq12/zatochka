<?php

namespace App\Infrastructure\SiteContent\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class SiteFaqItemModel extends Model
{
    protected $table = 'site_faq_items';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'answer_lines' => 'array',
        ];
    }
}
