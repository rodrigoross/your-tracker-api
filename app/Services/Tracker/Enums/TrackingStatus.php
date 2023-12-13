<?php

namespace App\Services\Tracker\Enums;

use Illuminate\Support\Facades\Log;

enum TrackingStatus: string
{
    case ELECTRONIC_REVIEW = 'Informações eletrônicas enviadas para análise da autoridade aduaneira';
    case POSTED = 'Objeto postado';
    case INSPECTION = 'Encaminhado para fiscalização aduaneira';
    case PENDING = 'Fiscalização aduaneira concluída - aguardando pagamento';
    case PAYED = 'Pagamento confirmado';
    case RECEIVED = 'Objeto recebido pelos Correios do Brasil';
    case EXITED = 'Saída do Centro Internacional';
    case IN_TRANSIT = 'Objeto encaminhado';
    case DELIVERING = 'Objeto saiu para entrega ao destinatário';
    case DELIVERED = 'Objeto entregue ao destinatário';
    case UNDEFINED = 'Status indefinido';

    public static function fromName(string $name): string
    {
        foreach (self::cases() as $status) {
            if ($name === $status->name) {
                return $status->value;
            }
        }

        Log::driver('tracker')->error("{$name} is not a valid name for enum " . self::class);

        return self::UNDEFINED->value;
    }

    public static function fromValue(string $value): string
    {
        foreach (self::cases() as $status) {
            if ($value === $status->value) {
                return $status->name;
            }
        }

        Log::driver('tracker')->error("$value is not a valid value for enum " . self::class);

        return self::UNDEFINED->name;
    }
}
