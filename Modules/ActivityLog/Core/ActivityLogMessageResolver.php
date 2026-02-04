<?php
namespace Modules\ActivityLog\Core;

use Modules\Accounts\Core\Helpers;

class ActivityLogMessageResolver
{
    public function resolve(string $logType, array $metadata): array
    {
        $metaType = $metadata['type'] ?? 'default';

        return [
            'key'    => "activitylog::messages.$logType.messages.$metaType",
            'params' => $this->resolveParams($metaType, $metadata),
        ];
    }

    protected function resolveParams(string $metaType, array $metadata): array
    {
        return match ($metaType) {
            'goal_created' => [
                'initialTarget' => Helpers::formatMoneyWithCurrency($metadata['initialTarget'] ?? 0, $metadata['currencyCode'], $metadata['currencySymbol']),
            ],

            'transfer'     => [
                'amount' => $metadata['amount'] ?? 0,
                'to'     => $metadata['to'] ?? '',
            ],

            default        => [],
        };
    }
}
