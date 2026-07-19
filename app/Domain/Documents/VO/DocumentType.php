<?php

namespace App\Domain\Documents\VO;

enum DocumentType: string
{
    case PrivacyPolicy = 'privacy_policy';
    case UserAgreement = 'user_agreement';
    case UsageRules = 'usage_rules';

    public function label(): string
    {
        return match ($this) {
            self::PrivacyPolicy => 'Политика конфиденциальности',
            self::UserAgreement => 'Пользовательское соглашение',
            self::UsageRules => 'Правила пользования',
        };
    }

    public function publicSlug(): string
    {
        return match ($this) {
            self::PrivacyPolicy => 'privacy-policy',
            self::UserAgreement => 'user-agreement',
            self::UsageRules => 'usage-rules',
        };
    }

    public static function fromPublicSlug(string $slug): ?self
    {
        return match ($slug) {
            'privacy-policy' => self::PrivacyPolicy,
            'user-agreement', 'terms-of-service' => self::UserAgreement,
            'usage-rules' => self::UsageRules,
            default => null,
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
    }
}
