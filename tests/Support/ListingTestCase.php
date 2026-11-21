<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\User\Entities\User;
use Tests\TestCase;

abstract class ListingTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('username')->nullable();
            $t->string('email')->nullable();
            $t->string('password')->nullable();
            $t->timestamps();
        });
        Schema::create('shared_roles', function (Blueprint $t) {
            $t->id();
            $t->string('code');
            $t->json('name');
        });
        Schema::create('shared_permissions', function (Blueprint $t) {
            $t->id();
            $t->string('code');
        });
        Schema::create('shared_permission_roles', function (Blueprint $t) {
            $t->unsignedBigInteger('shared_role_id');
            $t->unsignedBigInteger('shared_permission_id');
        });
        Schema::create('languages', function (Blueprint $t) {
            $t->id();
            $t->string('code');
        });
        DB::table('languages')->insert(['code' => 'en']);
        Schema::create('currencies', function (Blueprint $t) {
            $t->id();
            $t->string('code');
            $t->string('symbol');
            $t->json('name');
            $t->decimal('rate')->default(1);
        });
        Schema::create('accounts', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('type')->default('bank_account');
            $t->unsignedBigInteger('currency_id')->default(1);
            $t->decimal('balance')->default(0);
            $t->boolean('active')->default(true);
        });
        Schema::create('account_users', function (Blueprint $t) {
            $t->unsignedBigInteger('account_id');
            $t->unsignedBigInteger('user_id');
            $t->unsignedBigInteger('shared_role_id');
        });
        Schema::create('transactions', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('account_id');
            $t->unsignedBigInteger('user_id')->default(1);
            $t->unsignedBigInteger('category_id')->nullable();
            $t->date('date');
            $t->string('type')->default('expense');
            $t->string('status')->default('completed');
            $t->decimal('amount')->default(10);
            $t->text('description')->nullable();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->json('name')->nullable();
            $t->string('type')->default('expense');
            $t->boolean('is_default')->default(true);
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('icon')->nullable();
            $t->string('color')->nullable();
        });
        DB::table('users')->insert([['id' => 1, 'name' => 'Reader', 'username' => 'reader'], ['id' => 2, 'name' => 'Other', 'username' => 'other'], ['id' => 11, 'name' => 'Private', 'username' => 'private']]);
        DB::table('shared_roles')->insert([['id' => 1, 'code' => 'reader', 'name' => '{"en":"Reader","pt":"Leitor"}'], ['id' => 2, 'code' => 'hidden', 'name' => '{"en":"Hidden"}']]);
        $permissions = ['viewAccount', 'viewTransaction', 'viewDebt', 'viewDebtPayment', 'viewFinancialGoal', 'viewFinancialGoalTransaction'];
        foreach ($permissions as $index => $permission) {
            DB::table('shared_permissions')->insert(['id' => $index + 1, 'code' => $permission]);
            DB::table('shared_permission_roles')->insert(['shared_role_id' => 1, 'shared_permission_id' => $index + 1]);
        }
        DB::table('currencies')->insert(['id' => 1, 'code' => 'EUR', 'symbol' => '€', 'name' => '{"en":"Euro"}']);
        DB::table('accounts')->insert([['id' => 1, 'name' => 'Main'], ['id' => 2, 'name' => 'Private'], ['id' => 3, 'name' => 'No permission']]);
        DB::table('account_users')->insert([['account_id' => 1, 'user_id' => 1, 'shared_role_id' => 1], ['account_id' => 2, 'user_id' => 11, 'shared_role_id' => 1], ['account_id' => 3, 'user_id' => 1, 'shared_role_id' => 2]]);
        $this->actingAs((new User)->forceFill(['id' => 1, 'name' => 'Reader'])->setRelation('preferences', null));
    }

    protected function listingRequest(array $parameters = []): Request
    {
        $request = Request::create('/api/v1/test', 'GET', $parameters);
        $request->setUserResolver(fn () => auth()->user());

        return $request;
    }
}
