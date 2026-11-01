<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\Accounts\Http\Requests\TransactionUpdateRequest;
use Modules\Category\Entities\Category;
use Modules\Category\Http\Resources\CategoryResource;
use Modules\Category\Repositories\CategoryRepository;
use Modules\Category\Rules\SelectableCategory;
use Modules\RecurringTransaction\Http\Requests\UpdateRecurringTransactionRequest;
use Modules\User\Entities\User;
use Tests\TestCase;

class PersonalCategoriesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', fn (Blueprint $table) => $table->id());
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->string('type');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('code')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
        foreach (['transactions', 'recurring_transactions'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('account_id')->default(1);
                $table->unsignedBigInteger('user_id')->default(1);
                $table->string('type')->default('expense');
                $table->timestamp('deleted_at')->nullable();
            });
        }
        Schema::create('accounts', fn (Blueprint $table) => $table->id());
        DB::table('users')->insert([['id' => 1], ['id' => 2]]);
        DB::table('accounts')->insert(['id' => 1]);
        $this->actingAs((new User)->forceFill(['id' => 1])->setRelation('preferences', null));
        app()->setLocale('en');
    }

    private function category(array $overrides = []): Category
    {
        return Category::create(array_replace(['name' => 'Custom', 'type' => 'expense', 'user_id' => 1, 'is_default' => false], $overrides));
    }

    private function payload(array $overrides = []): array
    {
        return array_replace(['name' => 'My groceries', 'type' => 'expense', 'icon' => 'ShoppingBasket', 'color' => '#16a34a'], $overrides);
    }

    public function test_creation_assigns_ownership_and_preserves_personal_names(): void
    {
        $this->postJson('/api/v1/categories', $this->payload(['name' => "\u{00A0} My   groceries \u{00A0}"]))->assertCreated()
            ->assertJsonPath('data.name', 'My groceries')->assertJsonPath('data.type', 'expense')
            ->assertJsonPath('data.isDefault', false)->assertJsonPath('data.canManage', true);
        $this->assertDatabaseHas('categories', ['user_id' => 1, 'is_default' => 0]);
        foreach (['user_id' => 2, 'is_default' => true, 'code' => 'secret', 'parent_id' => 2, 'archived_at' => '2026-01-01'] as $field => $value) {
            $this->postJson('/api/v1/categories', $this->payload([$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
        }
    }

    public function test_the_fiftieth_is_allowed_but_the_next_is_rejected_and_defaults_do_not_count(): void
    {
        for ($i = 0; $i < 49; $i++) {
            $this->category(['name' => 'Personal '.$i]);
        }
        $this->category(['name' => ['en' => 'Default'], 'is_default' => true, 'user_id' => null]);
        $this->category(['name' => 'Other', 'user_id' => 2]);
        $this->postJson('/api/v1/categories', $this->payload())->assertCreated();
        $this->postJson('/api/v1/categories', $this->payload(['name' => 'Over limit']))->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->getJson('/api/v1/categories?type=revenue')->assertOk()->assertJsonPath('meta.personalCount', 50)->assertJsonPath('meta.remaining', 0)->assertJsonPath('meta.limit', 50);
    }

    public function test_listing_groups_visibility_and_type_and_includes_null_codes(): void
    {
        $default = $this->category(['name' => ['en' => 'Food', 'pt' => 'Alimentação'], 'is_default' => true, 'user_id' => null]);
        $own = $this->category();
        $this->category(['name' => 'Income', 'type' => 'revenue']);
        $this->category(['name' => 'Other', 'user_id' => 2]);
        $this->category(['name' => ['en' => 'Internal'], 'is_default' => true, 'code' => 'financialGoalContribution', 'user_id' => null]);
        $this->getJson('/api/v1/categories?type=expense')->assertOk()->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', (string) $default->id)->assertJsonPath('data.1.id', (string) $own->id)
            ->assertJsonPath('data.0.type', 'expense')->assertJsonPath('data.1.name', 'Custom');
        app()->setLocale('pt');
        $this->getJson('/api/v1/categories?type=expense')->assertJsonPath('data.0.name', 'Alimentação')->assertJsonPath('data.1.name', 'Custom');
        $this->getJson('/api/v1/categories?type=null')->assertUnprocessable();
    }

    public function test_duplicate_names_include_default_translations_archived_and_other_types(): void
    {
        $this->category(['name' => ['en' => 'Food', 'pt' => 'Alimentação'], 'is_default' => true, 'user_id' => null]);
        $archived = $this->category(['name' => 'My   Books']);
        $archived->archived_at = now();
        $archived->save();
        foreach ([' FOOD ', 'alimentação', 'my books'] as $name) {
            $this->postJson('/api/v1/categories', $this->payload(['name' => $name, 'type' => 'revenue']))->assertUnprocessable()->assertJsonValidationErrors('name');
        }
        $this->category(['name' => 'Other private', 'user_id' => 2]);
        $this->postJson('/api/v1/categories', $this->payload(['name' => 'Other private']))->assertCreated();
    }

    public function test_ownership_defaults_and_missing_ids_are_protected(): void
    {
        $other = $this->category(['user_id' => 2]);
        $default = $this->category(['is_default' => true, 'name' => ['en' => 'Default'], 'user_id' => null]);
        foreach ([$other, $default] as $category) {
            $this->putJson('/api/v1/categories/'.$category->id, ['name' => 'Changed'])->assertForbidden();
            $this->deleteJson('/api/v1/categories/'.$category->id)->assertForbidden();
            $this->patchJson('/api/v1/categories/'.$category->id.'/archive', ['is_archived' => true])->assertForbidden();
        }
        $this->getJson('/api/v1/categories/'.$other->id)->assertNotFound();
        $this->getJson('/api/v1/categories/999')->assertNotFound();
        $this->deleteJson('/api/v1/categories/999')->assertNotFound();
    }

    public function test_update_cannot_change_type_and_only_unused_categories_can_be_deleted(): void
    {
        $category = $this->category();
        $this->putJson('/api/v1/categories/'.$category->id, ['name' => 'Renamed', 'icon' => 'Gift', 'color' => '#AABBCC'])->assertOk()->assertJsonPath('data.name', 'Renamed');
        $this->putJson('/api/v1/categories/'.$category->id, ['name' => 'Changed', 'type' => 'revenue'])->assertUnprocessable();
        DB::table('transactions')->insert(['category_id' => $category->id]);
        $this->deleteJson('/api/v1/categories/'.$category->id)->assertUnprocessable()->assertJsonValidationErrors('category');
        $this->getJson('/api/v1/categories')->assertJsonPath('data.0.canDelete', false);
        $unused = $this->category(['name' => 'Unused']);
        $this->deleteJson('/api/v1/categories/'.$unused->id)->assertOk();
        $this->assertDatabaseMissing('categories', ['id' => $unused->id]);
    }

    public function test_archive_preserves_history_and_count_and_restore_is_supported(): void
    {
        $category = $this->category();
        DB::table('recurring_transactions')->insert(['category_id' => $category->id, 'deleted_at' => now()]);
        $this->deleteJson('/api/v1/categories/'.$category->id)->assertUnprocessable();
        $this->patchJson('/api/v1/categories/'.$category->id.'/archive', ['is_archived' => true])->assertOk()->assertJsonPath('data.isArchived', true);
        $this->getJson('/api/v1/categories')->assertJsonCount(0, 'data')->assertJsonPath('meta.personalCount', 1);
        $this->getJson('/api/v1/categories?includeArchived=1')->assertJsonCount(1, 'data')->assertJsonPath('data.0.canDelete', false);
        $this->assertDatabaseHas('recurring_transactions', ['category_id' => $category->id]);
        $this->patchJson('/api/v1/categories/'.$category->id.'/archive', ['is_archived' => false])->assertOk()->assertJsonPath('data.isArchived', false);
        $this->getJson('/api/v1/categories')->assertJsonCount(1, 'data');
    }

    public function test_invalid_fields_return_json_and_localized_messages_without_accept(): void
    {
        app()->setLocale('pt');
        $this->post('/api/v1/categories', $this->payload(['name' => '  ', 'color' => 'red', 'icon' => '<svg>']))
            ->assertUnprocessable()->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('errors.name.0', 'Indica o nome da categoria.')->assertJsonValidationErrors(['color', 'icon']);
    }

    public function test_transaction_and_recurring_creation_reject_others_archived_and_wrong_type(): void
    {
        $other = $this->category(['user_id' => 2]);
        $archived = $this->category(['name' => 'Archived']);
        $archived->archived_at = now();
        $archived->save();
        $income = $this->category(['name' => 'Income', 'type' => 'revenue']);
        foreach ([$other, $archived, $income] as $category) {
            $this->postJson('/api/v1/transactions', ['account_id' => 1, 'category_id' => $category->id, 'amount' => 10, 'type' => 'expense', 'date' => '2026-11-01', 'status' => 'completed'])
                ->assertUnprocessable()->assertJsonValidationErrors('category_id');
            $this->postJson('/api/v1/recurrings', ['account_id' => 1, 'category_id' => $category->id, 'amount' => 10, 'type' => 'expense', 'title' => 'Rent', 'frequency' => 'monthly', 'start_date' => '2026-11-01'])
                ->assertUnprocessable()->assertJsonValidationErrors('category_id');
        }
    }

    public function test_editing_a_shared_or_archived_category_preserves_the_original_assignment_only(): void
    {
        Route::put('/__test/transactions/{transaction}', function (TransactionUpdateRequest $request) {
            return response()->json($request->validated());
        });
        Route::put('/__test/recurrings/{recurring}', function (UpdateRecurringTransactionRequest $request) {
            return response()->json($request->validated());
        });
        $original = $this->category(['name' => 'Shared archived', 'user_id' => 2]);
        $original->archived_at = now();
        $original->save();
        $other = $this->category(['name' => 'Not assigned', 'user_id' => 2]);
        foreach (['transactions', 'recurring_transactions'] as $table) {
            DB::table($table)->insert(['id' => 1, 'category_id' => $original->id, 'type' => 'expense']);
            $url = '/__test/'.($table === 'transactions' ? 'transactions' : 'recurrings').'/1';
            $this->putJson($url, ['category_id' => $original->id, 'amount' => 10, 'date' => '2026-11-01'])->assertOk();
            $this->putJson($url, ['category_id' => $other->id, 'amount' => 10, 'date' => '2026-11-01'])->assertUnprocessable()->assertJsonValidationErrors('category_id');
        }
    }

    public function test_empty_protected_fields_do_not_remove_ownership_or_change_type(): void
    {
        $category = $this->category();
        $this->putJson('/api/v1/categories/'.$category->id, ['name' => 'Safe', 'user_id' => null, 'type' => null, 'code' => null, 'archived_at' => null])->assertOk();
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'user_id' => 1, 'type' => 'expense', 'name' => json_encode('Safe')]);
    }

    public function test_historical_filters_include_shared_categories_without_exposing_private_accounts(): void
    {
        Schema::create('accounts_view', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->string('user_ids');
        });
        DB::connection()->getPdo()->sqliteCreateFunction('FIND_IN_SET', fn ($id, $members) => in_array((string) $id, explode(',', $members), true) ? 1 : 0, 2);
        DB::table('accounts_view')->insert([['id' => 1, 'user_ids' => '1, 2'], ['id' => 2, 'user_ids' => '2']]);
        $shared = $this->category(['name' => 'Shared category', 'user_id' => 2]);
        $private = $this->category(['name' => 'Private category', 'user_id' => 2]);
        foreach (['transactions', 'recurring_transactions'] as $table) {
            DB::table($table)->insert([
                ['category_id' => $shared->id, 'account_id' => 1, 'user_id' => 2],
                ['category_id' => $private->id, 'account_id' => 2, 'user_id' => 2],
            ]);
        }
        $request = Request::create('/');
        $request->setUserResolver(fn () => auth()->user());
        $repository = app(CategoryRepository::class);
        $this->assertEquals([$shared->id], $repository->allUserTransactionsCategories($request)->modelKeys());
        $this->assertEquals([$shared->id], $repository->allUserRecurringsCategories($request)->modelKeys());
        $this->getJson('/api/v1/categories')->assertJsonCount(0, 'data');
    }

    public function test_archive_migration_upgrades_and_rolls_back_without_losing_categories(): void
    {
        $category = $this->category();
        Schema::table('categories', fn (Blueprint $table) => $table->dropColumn('archived_at'));
        $migration = require base_path('Modules/Category/Database/Migrations/2026_11_01_110000_add_archived_at_to_categories_table.php');
        $migration->up();
        $this->assertTrue(Schema::hasColumn('categories', 'archived_at'));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $migration->down();
        $this->assertFalse(Schema::hasColumn('categories', 'archived_at'));
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_assignment_is_revalidated_under_the_write_lock(): void
    {
        $category = $this->category();
        $rule = new SelectableCategory(1, 'expense');
        DB::transaction(fn () => $rule->lockAndValidate($category->id));
        $category->archived_at = now();
        $category->save();
        $this->expectException(ValidationException::class);
        DB::transaction(fn () => $rule->lockAndValidate($category->id));
    }

    public function test_authentication_is_required(): void
    {
        $this->withMiddleware();
        auth()->forgetGuards();
        $this->getJson('/api/v1/categories')->assertUnauthorized();
        $this->postJson('/api/v1/categories', $this->payload())->assertUnauthorized();
    }

    public function test_category_assignments_reject_arrays_instead_of_reaching_persistence(): void
    {
        $category = $this->category();
        foreach (['transactions', 'recurrings'] as $endpoint) {
            $this->postJson('/api/v1/'.$endpoint, [
                'account_id' => 1, 'category_id' => [$category->id], 'amount' => 10,
                'type' => 'expense', 'date' => '2026-11-01', 'title' => 'Test',
                'frequency' => 'monthly', 'start_date' => '2026-11-01',
            ])->assertUnprocessable()->assertJsonValidationErrors('category_id');
        }
    }

    public function test_shared_history_can_render_a_personal_category_without_granting_management(): void
    {
        $category = $this->category(['user_id' => 2, 'name' => 'Shared groceries']);
        $data = (new CategoryResource($category))->resolve(request());
        $this->assertSame('Shared groceries', $data['name']);
        $this->assertFalse($data['canManage']);
    }
}
