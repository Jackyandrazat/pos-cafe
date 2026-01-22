<?php

namespace App\Enums;

enum LoyaltyChallengeType: string
{
    case WeeklyVisits = 'weekly_visits';
    case NewVariant = 'new_variant';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::WeeklyVisits => 'Weekly Visits',
            self::NewVariant => 'New Variant Explorer',
            self::Custom => 'Custom Challenge',
        };
    }
}
