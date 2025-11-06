<?php

namespace Modules\Currency\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Currency\Entities\Currency;

class FetchApiCurrenciesDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:fetch-daily';
    protected $description = 'Faz um pedido à API e salva os dados diariamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $apiToken = config('currency.services.external.api_key');
            $response = Http::get("https://v6.exchangerate-api.com/v6/$apiToken/latest/USD");

            if ($response->successful()) {
                $data = $response->json();

                foreach ($data['conversion_rates'] as $currency => $rate) {
                    Currency::where("code", $currency)->update(['rate' => $rate]);
                }

                $this->info('Dados salvos com sucesso.');
            } else {
                $this->error('Erro ao buscar dados da API.');
            }
        } catch (\Exception $e) {
            Log::error($e);
        }
    }
}
