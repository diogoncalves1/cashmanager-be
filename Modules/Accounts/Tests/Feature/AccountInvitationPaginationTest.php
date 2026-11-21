<?php

namespace Modules\Accounts\Tests\Feature;

use Tests\Support\ChecksInvitationPagination;
use Tests\Support\ListingTestCase;

class AccountInvitationPaginationTest extends ListingTestCase
{
    use ChecksInvitationPagination;

    public function test_sent_and_received_invitations_preserve_the_authenticated_scope(): void
    {
        $this->checkInvitationPagination('accounts', 'account_user_invites', 'account_id', 'accounts');
    }
}
