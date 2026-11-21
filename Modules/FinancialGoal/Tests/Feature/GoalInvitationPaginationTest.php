<?php

namespace Modules\FinancialGoal\Tests\Feature;

use Tests\Support\ChecksInvitationPagination;
use Tests\Support\ListingTestCase;

class GoalInvitationPaginationTest extends ListingTestCase
{
    use ChecksInvitationPagination;

    public function test_sent_and_received_invitations_preserve_the_authenticated_scope(): void
    {
        $this->checkInvitationPagination('financial-goals', 'financial_goal_user_invites', 'financial_goal_id', 'financial_goals');
    }
}
