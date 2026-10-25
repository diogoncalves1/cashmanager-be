<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Modules\RecurringTransaction\Http\Requests\StoreRecurringTransactionRequest;
use Modules\RecurringTransaction\Http\Requests\UpdateRecurringTransactionRequest;
use Tests\TestCase;

class RecurringTransactionValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        foreach (['accounts', 'categories'] as $table) {
            Schema::create($table, fn (Blueprint $blueprint) => $blueprint->id());
            DB::table($table)->insert(['id' => 1]);
        }
    }

    private function validPayload(): array
    {
        return [
            'account_id' => 1,
            'category_id' => null,
            'title' => 'Monthly subscription',
            'amount' => 10,
            'type' => 'expense',
            'frequency' => 'monthly',
            'custom_frequency_days' => null,
            'start_date' => '2026-10-25',
            'end_date' => null,
            'is_linked' => false,
            'linked_type' => null,
            'linked_id' => null,
        ];
    }

    public function test_store_returns_portuguese_field_errors_as_json_without_an_accept_header(): void
    {
        app()->setLocale('pt');
        $this->post('/api/v1/recurrings', [])
            ->assertUnprocessable()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeaderMissing('Location')
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.account_id.0', 'Seleciona a conta da transação recorrente.')
            ->assertJsonPath('errors.title.0', 'Indica um título para a transação recorrente.')
            ->assertJsonPath('errors.amount.0', 'Indica o valor da transação recorrente.')
            ->assertJsonPath('errors.frequency.0', 'Seleciona a frequência da transação recorrente.');
    }

    public function test_update_returns_english_errors_without_redirecting(): void
    {
        app()->setLocale('en');
        $this->put('/api/v1/recurrings/1', [
            'amount' => 0,
            'title' => ['invalid'],
            'end_date' => 'invalid',
            'custom_frequency_days' => 1.5,
        ])->assertUnprocessable()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeaderMissing('Location')
            ->assertJsonPath('errors.amount.0', 'The transaction amount must be at least 0.01.')
            ->assertJsonPath('errors.title.0', 'The title must be text.')
            ->assertJsonPath('errors.end_date.0', 'Enter a valid end date.')
            ->assertJsonPath('errors.custom_frequency_days.0', 'The repeat interval must be a whole number of days.');
    }

    public function test_custom_schedule_and_link_errors_are_explained_in_both_languages(): void
    {
        foreach (['pt', 'en'] as $locale) {
            app()->setLocale($locale);
            $response = $this->postJson('/api/v1/recurrings', array_replace($this->validPayload(), [
                'frequency' => 'custom',
                'is_linked' => true,
            ]))->assertUnprocessable();

            foreach (['custom_frequency_days', 'linked_type', 'linked_id'] as $field) {
                $response->assertJsonPath("errors.{$field}.0", __("recurringtransaction::validation.messages.{$field}.required_if"));
            }
        }
    }

    public function test_unlinked_payload_allows_null_optional_link_and_category_fields(): void
    {
        $request = new StoreRecurringTransactionRequest;
        $validator = Validator::make($this->validPayload(), $request->rules(), $request->messages());
        $this->assertFalse($validator->fails(), $validator->errors()->toJson());
    }

    public function test_invalid_dates_and_deleted_options_have_specific_errors(): void
    {
        app()->setLocale('pt');
        $this->postJson('/api/v1/recurrings', array_replace($this->validPayload(), [
            'account_id' => 999,
            'category_id' => 999,
            'end_date' => '2026-10-24',
        ]))->assertUnprocessable()
            ->assertJsonPath('errors.account_id.0', 'A conta selecionada já não está disponível.')
            ->assertJsonPath('errors.category_id.0', 'A categoria selecionada já não está disponível.')
            ->assertJsonPath('errors.end_date.0', 'A data de fim deve ser posterior à data de início.');
    }

    public function test_every_failing_rule_has_a_localized_message_in_store_and_update(): void
    {
        foreach (['pt', 'en'] as $locale) {
            app()->setLocale($locale);
            foreach ([new StoreRecurringTransactionRequest, new UpdateRecurringTransactionRequest] as $request) {
                $messages = $request->messages();
                foreach ($request->rules() as $field => $rules) {
                    foreach (explode('|', $rules) as $rule) {
                        $name = explode(':', $rule)[0];
                        if (! in_array($name, ['nullable', 'sometimes'])) {
                            $this->assertArrayHasKey("{$field}.{$name}", $messages);
                            $this->assertNotSame('', trim($messages["{$field}.{$name}"]));
                        }
                    }
                }
            }
        }
    }

    public function test_overlong_titles_are_rejected_before_the_database_write(): void
    {
        app()->setLocale('pt');
        foreach (['postJson', 'putJson'] as $method) {
            $this->{$method}('/api/v1/recurrings'.($method === 'putJson' ? '/1' : ''), array_replace($this->validPayload(), [
                'title' => str_repeat('a', 256),
            ]))->assertUnprocessable()
                ->assertJsonPath('errors.title.0', 'O título não pode ter mais de 255 caracteres.');
        }
    }
}
