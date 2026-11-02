<?php

namespace Modules\FinancialGoal\Services;

use Illuminate\Support\Facades\DB;
use Modules\FinancialGoal\Entities\FinancialGoalTransaction;

class BackfillGoalRequestReceipts
{
    public function run(): int
    {
        return DB::transaction(function () {
            $changed = 0;
            FinancialGoalTransaction::with('transaction')->orderBy('id')->lockForUpdate()->chunkById(200, function ($entries) use (&$changed) {
                $byEntry = DB::table('financial_goal_request_receipts')->whereIn('entry_id', $entries->pluck('id'))->get()->groupBy('entry_id');
                $keys = [];
                foreach ($entries as $entry) {
                    $receipts = $byEntry->get($entry->id, collect());
                    if ($receipts->count() > 1) {
                        $this->conflict($entry->id, 'more than one receipt references the movement');
                    }
                    $savedKey = $receipts->first()?->request_key;
                    if ($receipts->isNotEmpty() && ! $this->hasValue($savedKey)) {
                        $this->conflict($entry->id, 'the existing receipt has no key');
                    }
                    if ($this->hasValue($entry->request_key) && $this->hasValue($savedKey) && $entry->request_key !== $savedKey) {
                        $this->conflict($entry->id, 'the movement and its receipt have different keys');
                    }
                    $keys[$entry->id] = $this->hasValue($entry->request_key) ? $entry->request_key : ($this->hasValue($savedKey) ? $savedKey : 'historical-goal-entry:'.$entry->id);
                }
                $byKey = DB::table('financial_goal_request_receipts')->whereIn('request_key', array_values($keys))->get()->keyBy('request_key');
                foreach ($entries as $entry) {
                    $key = $keys[$entry->id];
                    $receipt = $byKey->get($key);
                    if ($receipt && ((string) $receipt->entry_id !== (string) $entry->id
                        || (string) $receipt->financial_goal_id !== (string) $entry->financial_goal_id
                        || (string) $receipt->user_id !== (string) $entry->user_id)) {
                        $this->conflict($entry->id, 'the key belongs to another movement, goal or user');
                    }
                    if ($this->hasValue($entry->request_hash) && $this->hasValue($receipt?->request_hash) && $entry->request_hash !== $receipt->request_hash) {
                        $this->conflict($entry->id, 'the movement and its receipt have different hashes');
                    }
                    $hash = $this->hasValue($entry->request_hash) ? $entry->request_hash : ($this->hasValue($receipt?->request_hash) ? $receipt->request_hash : $this->fingerprint($entry, $key));
                    if (! preg_match('/^[a-f0-9]{64}$/i', $hash)) {
                        $this->conflict($entry->id, 'the existing hash is not SHA-256');
                    }
                    $updateEntry = $entry->request_key !== $key || $entry->request_hash !== $hash;
                    if ($updateEntry) {
                        // Metadata repair must not rewrite historical business timestamps.
                        DB::table('financial_goal_transactions')->where('id', $entry->id)->update(['request_key' => $key, 'request_hash' => $hash]);
                    }
                    if (! $receipt) {
                        DB::table('financial_goal_request_receipts')->insert([
                            'request_key' => $key, 'request_hash' => $hash, 'entry_id' => $entry->id,
                            'financial_goal_id' => $entry->financial_goal_id, 'user_id' => $entry->user_id,
                        ]);
                    } elseif (! $this->hasValue($receipt->request_hash)) {
                        DB::table('financial_goal_request_receipts')->where('request_key', $key)->update(['request_hash' => $hash]);
                    }
                    if ($updateEntry || ! $receipt || ! $this->hasValue($receipt->request_hash)) {
                        $changed++;
                    }
                }
            });

            return $changed;
        }, 3);
    }

    private function fingerprint(FinancialGoalTransaction $entry, string $key): string
    {
        // Old clients never sent an idempotency key. Reconstruct one stable request
        // from the stored movement; existing request/receipt hashes always take priority.
        $accountId = $entry->account_id ?? $entry->transaction?->account_id;
        $data = [
            'financial_goal_id' => (string) $entry->financial_goal_id,
            'account_id' => $accountId === null ? null : (string) $accountId,
            'amount' => number_format(GoalReservations::cents($entry->amount) / 100, 2, '.', ''),
            'date' => $entry->date, 'type' => $entry->type, 'status' => $entry->status,
            'description' => $entry->description, 'request_key' => $key, 'user_id' => (int) $entry->user_id,
        ];
        $transactionId = $entry->type === 'usage' && ! $entry->expense_created ? $entry->transaction_id : null;
        $categoryId = $entry->type === 'usage' && $entry->expense_created ? $entry->transaction?->category_id : null;

        return hash('sha256', json_encode([$data,
            $transactionId === null ? null : (string) $transactionId,
            $categoryId === null ? null : (string) $categoryId,
        ], JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR));
    }

    private function hasValue(?string $value): bool
    {
        return $value !== null && $value !== '';
    }

    private function conflict(int|string $id, string $reason): never
    {
        throw new \RuntimeException('Cannot backfill request receipt for goal movement '.$id.': '.$reason.'.');
    }
}
