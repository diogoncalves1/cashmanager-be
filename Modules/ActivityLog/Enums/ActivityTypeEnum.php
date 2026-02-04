<?php
namespace Modules\ActivityLog\Enums;

enum ActivityTypeEnum: string {
    case financial_goal = 'FinancialGoal';
    case debt           = 'Debt';
    case account        = 'Account';
}
