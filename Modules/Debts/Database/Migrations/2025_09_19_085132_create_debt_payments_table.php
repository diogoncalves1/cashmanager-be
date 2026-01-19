<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Category\Entities\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('debt_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('date');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->decimal('amount', 15);
            $table->text('description')->nullable();
            $table->decimal('interest_rate', 15, 4);
            $table->timestamps();

            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('debt_id')->references('id')->on('debts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        $permissions = [
            ['name' => 'Ver Pagamento de Dívida', 'code' => 'viewDebtPayment', 'category' => 'Pagamento de Dívidas'],
            ['name' => 'Adicionar Pagamento de Dívida', 'code' => 'storeDebtPayment', 'category' => 'Pagamento de Dívidas'],
            ['name' => 'Editar Pagamento de Dívida', 'code' => 'editDebtPayment', 'category' => 'Pagamento de Dívidas'],
            ['name' => 'Apagar Pagamento de Dívida', 'code' => 'destroyDebtPayment', 'category' => 'Pagamento de Dívidas'],
            ['name' => 'Confirmar Pagamento de Dívida', 'code' => 'confirmDebtPayment', 'category' => 'Pagamento de Dívidas'],
        ];

        foreach ($permissions as $permission) {
            $id                = DB::table('shared_permissions')->insertGetId($permission);
            $rolePermissions[] = ['shared_permission_id' => $id, 'shared_role_id' => 1];
        }

        DB::table('shared_permission_roles')->insert($rolePermissions);

        $categories = [
            [
                'icon'       => 'CreditCard',
                'name'       => ['en' => 'Debt Payment', 'pt' => 'Pagamento de dívida'],
                'code'       => 'debtPayment',
                'type'       => 'expense',
                'color'      => '#EA580C',
                'is_default' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('debt_payments');

        $permissions = ['viewDebtPayment', 'storeDebtPayment', 'editDebtPayment', 'destroyDebtPayment', "confirmDebtPayment"];

        foreach ($permissions as $permission) {
            DB::table("shared_permissions")->where("code", $permission)->delete();
        }

        $categories = ['debtPayment'];

        foreach ($categories as $category) {
            DB::table("categories")->where("code", $category)->delete();
        }
    }
};
