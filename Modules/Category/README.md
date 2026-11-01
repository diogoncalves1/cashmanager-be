# Personal transaction categories

Related issues: diogoncalves1/cashmanager-be#116 and diogoncalves1/cashmanager-web#248.

## Product rules

Defaults (`is_default = true`) are managed by the administration. The authenticated user can create up to 50 personal categories in total across both types, including archived categories. Defaults do not count. The personal API does not allow changing ownership, default status, codes, hierarchy or type after creation.

Personal names are trimmed and whitespace is collapsed. Case-insensitive duplicates are rejected against all of the user's personal names (including archived names) and all default translations, across both movement types. Other users' private names do not block creation. Names have at most 100 characters; colors use six-digit hex and icons come from the frontend's existing icon map.

Only the owner can manage or select personal categories. Other members of shared accounts can see them in historical movements and their filters. Updating an existing transaction or recurring entry can retain its original category even if it belongs to another member or is archived; it cannot assign a different private category. New assignments must match the movement type and exclude internal financial-goal category codes.

Archiving hides categories from new selections but leaves existing transactions and recurring generation intact. Restoring makes them selectable again. Permanent deletion is allowed only with no transactions or recurring references, including soft-deleted recurring entries. Name, icon and color edits also update the presentation of existing movements; type is immutable.

The user explicitly confirmed owner-only selection with visibility on shared-account movements, defaults excluded from the quota, archival of used categories with archived categories still counted, and case/whitespace-insensitive duplicate prevention against personal and default names. Other details above, including type immutability and deletion of unused categories, are documented implementation choices.

## API

All routes use `auth:sanctum` and `setlocale` under `/api/v1`.

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/categories?type=expense&includeArchived=1` | All visible categories; optional type; archived excluded by default |
| POST | `/categories` | Create a personal category |
| GET | `/categories/{id}` | Show an owned or default category |
| PUT | `/categories/{id}` | Edit an owned category's name/icon/color |
| PATCH | `/categories/{id}/archive` | Set `is_archived` to true or false |
| DELETE | `/categories/{id}` | Delete an unused owned category |

Creation payload:

```json
{"name":"Weekly groceries","type":"expense","icon":"ShoppingBasket","color":"#15803d"}
```

Update payload has the same editable fields, omitting `type`. `icon` and `color` may be null. The list is unpaginated and retains the existing top-level `data` array. `type` is the stable raw value `expense` or `revenue`; `typeTranslated` is the localized label. Internal financial-goal codes are excluded from selection/management lists.

```json
{
  "success": true,
  "data": [{
    "id":"42", "name":"Weekly groceries", "type":"expense", "typeTranslated":"Expense",
    "icon":"ShoppingBasket", "color":"#15803d",
    "isDefault":false, "isArchived":false, "canManage":true, "canDelete":true
  }],
  "meta":{"personalCount":1,"limit":50,"remaining":49}
}
```

Metadata counts all personal categories, independently of type and archive filters. Resource flags do not expose owner IDs. Mutations return the standard `success`, `message`, `data`, `errors` envelope; creation returns 201, other successful mutations 200. Validation and quota errors return 422 with field `errors`; ownership failures return 403 and missing/invisible detail IDs return 404. Requests should send `Accept: application/json`. Category form validation also returns JSON without that header.

Creation/update/archive/deletion serialize on the owner user row. Assignments from API form requests are revalidated while holding user/category locks inside the transaction to protect against concurrent archival/deletion. Trusted internal recurring/goal/debt requests preserve their existing behavior and references.

## Deployment and tests

Apply `2026_11_01_110000_add_archived_at_to_categories_table.php` with `php artisan migrate` before releasing the frontend. It adds nullable `archived_at` and an owner/default index without rewriting existing categories. The module provider loads the case-sensitive `Database/Migrations` path. Rollback removes the new column/index; take the new frontend out of service before rolling back.

`php artisan test --filter=PersonalCategoriesTest` covers ownership, quotas, duplicates, list filtering, lifecycle/history, shared account filters, assignment validation and migration up/down using isolated SQLite. The MySQL row-lock concurrency behavior is implemented but is not exercised by SQLite tests. Run the recurring, account overview, authentication and translation suites as regressions.
