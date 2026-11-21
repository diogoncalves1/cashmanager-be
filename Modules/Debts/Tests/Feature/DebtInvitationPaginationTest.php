<?php

namespace Modules\Debts\Tests\Feature;

use Tests\Support\ChecksInvitationPagination;
use Tests\Support\ListingTestCase;

class DebtInvitationPaginationTest extends ListingTestCase
{
    use ChecksInvitationPagination;

    public function test_sent_and_received_invitations_preserve_the_authenticated_scope(): void
    {
        $this->checkInvitationPagination('debts', 'debt_user_invites', 'debt_id', 'debts');
    }
}
