<?php

namespace App\Modules\Conversation\Enums;

/**
 * Why the strategist asked the team to take over.
 */
enum HandoverReason: string
{
    case SpecialRequest = 'special_request';
    case Complaint = 'complaint';
    case GroupConsultation = 'group_consultation';
    case StrategyWorkshop = 'strategy_workshop';
    case CustomScope = 'custom_scope';
    case PaymentIssue = 'payment_issue';
    case LowConfidence = 'low_confidence';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
