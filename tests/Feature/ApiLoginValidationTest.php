<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\User\Entities\User;
use Tests\TestCase;

class ApiLoginValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_unknown_email_returns_validation_errors_instead_of_a_server_error(): void
    {
        $this->postJson('/api/v1/login', [
            'email' => 'unknown@example.test',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.email.0', 'Credenciais invalidas.');
    }

    public function test_wrong_password_returns_validation_errors_for_an_existing_user(): void
    {
        User::withoutEvents(fn () => User::forceCreate([
            'name' => 'Test User',
            'email' => 'verified@example.test',
            'password' => 'correct-password',
            'email_verified_at' => now(),
        ]));

        $this->postJson('/api/v1/login', [
            'email' => 'verified@example.test',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Credenciais invalidas.');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_missing_credentials_return_field_validation_errors(): void
    {
        $this->postJson('/api/v1/login', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['email', 'password']]);
    }

    public function test_password_must_be_a_string(): void
    {
        $this->postJson('/api/v1/login', [
            'email' => 'verified@example.test',
            'password' => ['invalid'],
        ])->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['password']]);
    }

    public function test_email_verification_failure_keeps_its_forbidden_status(): void
    {
        User::withoutEvents(fn () => User::forceCreate([
            'name' => 'Test User',
            'email' => 'unverified@example.test',
            'password' => 'correct-password',
        ]));

        $this->postJson('/api/v1/login', [
            'email' => 'unverified@example.test',
            'password' => 'correct-password',
        ])->assertForbidden()
            ->assertJsonPath('message', 'Valide o seu email antes de efetuar login.');
    }

    public function test_invalid_credentials_return_json_even_without_an_accept_header(): void
    {
        $this->post('/api/v1/login', [
            'email' => 'unknown@example.test',
            'password' => 'wrong-password',
        ])->assertUnprocessable()
            ->assertHeader('Content-Type', 'application/json');
    }
}
