<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalDocument extends Model
{
    use HasFactory;

    public const TYPE_PRIVACY_POLICY = 'privacy_policy';

    public const TYPE_USER_AGREEMENT = 'user_agreement';

    public const TYPE_PERSONAL_DATA_PROCESSING = 'personal_data_processing';

    public const TYPE_TERMS_OF_USE = 'terms_of_use';

    protected $fillable = [
        'company_id',
        'type',
        'title',
        'content',
        'version',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_PRIVACY_POLICY => 'Политика конфиденциальности',
            self::TYPE_USER_AGREEMENT => 'Пользовательское соглашение',
            self::TYPE_PERSONAL_DATA_PROCESSING => 'Обработка перс. данных',
            self::TYPE_TERMS_OF_USE => 'Условия использования ресурса',
        ];
    }
}
