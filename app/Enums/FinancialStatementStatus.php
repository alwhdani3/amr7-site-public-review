<?php

namespace App\Enums;

enum FinancialStatementStatus: string
{
    case NEW = 'new';
    case WAITING_DOCS = 'waiting_docs';
    case IN_REVIEW = 'in_review';
    case CLIENT_APPROVAL = 'client_approval';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
    case MOC_APPROVAL = 'moc_approval';

    public function label(): string
    {
        return match ($this) {
            self::NEW             => 'جديد',
            self::WAITING_DOCS    => 'بانتظار المستندات',
            self::IN_REVIEW       => 'قيد مراجعة الفريق',
            self::CLIENT_APPROVAL => 'بانتظار اعتماد العميل',
            self::COMPLETED       => 'مكتمل',
            self::CLOSED          => 'مغلق',
            self::CANCELLED       => 'ملغي',
            self::MOC_APPROVAL    => 'بانتظار اعتماد وزارة التجارة',
        };
    }
}
