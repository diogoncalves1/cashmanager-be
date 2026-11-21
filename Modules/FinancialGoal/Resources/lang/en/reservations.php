<?php

return [
    'deletion_changed' => 'The goal movements changed. Refresh and try again.',
    'deletion_pending' => 'Resolve pending movements before releasing the reserves and cancelling the goal.',

    'historical_linked' => 'Manage this historical goal movement from its financial goal to keep amounts consistent.',
    'target_locked' => 'Reopen the goal before changing its completed target amount.',
    'account_history' => 'An account with reservation history cannot be deleted. You can deactivate it and release its remaining reserves.',
    'currency' => 'The account and goal must use the same currency.',
    'inactive' => 'Choose an active account.',
    'retry' => 'This request was already used for a different operation. Refresh and try again.',
    'closed' => 'This goal is closed. You can still release its remaining reserve.',
    'date' => 'Check the date and movement status. Future movements cannot be confirmed.',
    'usage_completed' => 'Only completed expenses can use a reservation.',
    'amount' => 'Enter a positive amount.',
    'reserved' => 'The amount exceeds this goal’s reserve in the selected account.',
    'allocated' => 'This amount exceeds the unallocated portion of the expense.',
    'expense' => 'Choose a completed expense from the selected account.',
    'category' => 'Choose an expense category.',
    'immutable' => 'Completed reservations are kept in history. Use an explicit reserve or release to correct them.',
    'currency_locked' => 'The currency cannot change after movements have been recorded.',
    'history' => 'A goal with movement history cannot be deleted. Cancel it after releasing its reserves.',
    'release_first' => 'Release the remaining reserves and remove pending movements before cancelling.',
    'pending' => 'Resolve pending movements before completing this goal.',
    'linked' => 'This expense is associated with a goal. Unlink its goal usages before editing or deleting it.',
    'conversion' => 'The previous movements could not be reconciled automatically. Review their accounts, currencies and amounts.',
];
