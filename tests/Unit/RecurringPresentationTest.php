<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use Mockery;
use Modules\Accounts\Entities\Account;
use Modules\RecurringTransaction\Entities\RecurringTransaction;
use Modules\RecurringTransaction\Http\Resources\RecurringTransactionResource;
use Modules\RecurringTransaction\Policies\RecurringTransactionPolicy;
use Modules\User\Entities\User;
use Tests\TestCase;

class RecurringPresentationTest extends TestCase
{
    public function test_first_occurrence_uses_the_start_date(): void
    {
        $recurring = new RecurringTransaction(['start_date' => '2026-10-15', 'frequency' => 'monthly']);
        $this->assertSame('2026-10-15', $recurring->next_occurrence);
    }

    public function test_next_occurrence_follows_the_same_schedule_as_the_generator(): void
    {
        foreach (['daily' => '2026-10-10', 'weekly' => '2026-10-16', 'monthly' => '2026-11-09', 'yearly' => '2027-10-09', 'custom' => '2026-10-12'] as $frequency => $expected) {
            $recurring = new RecurringTransaction(['start_date' => '2026-01-09', 'last_generated_at' => '2026-10-09', 'frequency' => $frequency, 'custom_frequency_days' => 3]);
            $this->assertSame($expected, $recurring->next_occurrence);
            $this->assertSame('2026-10-09', $recurring->last_generated_at->toDateString());
        }
    }

    public function test_occurrences_do_not_continue_beyond_the_end_date(): void
    {
        $recurring = new RecurringTransaction(['start_date' => '2026-10-01', 'last_generated_at' => '2026-10-09', 'end_date' => '2026-10-10', 'frequency' => 'daily']);
        $this->assertSame('2026-10-10', $recurring->next_occurrence);
        $recurring->last_generated_at = '2026-10-10';
        $this->assertNull($recurring->next_occurrence);
    }

    public function test_the_detail_resource_exposes_numeric_amount_and_next_date(): void
    {
        $recurring = new RecurringTransaction(['amount' => '12.95', 'frequency' => 'monthly', 'start_date' => '2026-10-09', 'is_active' => true, 'is_linked' => false]);
        $data = (new RecurringTransactionResource($recurring))->resolve(Request::create('/'));
        $this->assertSame(12.95, $data['amount']);
        $this->assertSame('2026-10-09', $data['nextOccurrence']);
        $this->assertTrue($data['isActive']);
    }

    public function test_edit_and_delete_are_denied_without_account_membership(): void
    {
        $account = Mockery::mock(Account::class)->makePartial();
        $account->shouldReceive('userSharedRole')->andReturnNull();
        $recurring = (new RecurringTransaction)->setRelation('account', $account);
        $user = new User;
        $user->id = 123;
        $policy = new RecurringTransactionPolicy;
        $this->assertFalse($policy->update($user, $recurring));
        $this->assertFalse($policy->delete($user, $recurring));
    }
}
