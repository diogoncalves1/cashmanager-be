<?php
namespace Modules\Currency\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Currency\Core\Helpers;
use Modules\Currency\Entities\Currency;
use Modules\Language\Repositories\LanguageRepository;

class CurrencyRepository implements RepositoryInterface
{
    protected LanguageRepository $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function all()
    {
        return Currency::all();
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $input = $request->only(['code', 'symbol']);

            $languages = $this->languageRepository->allCodes();

            foreach ($languages as $language) {
                $name[$language] = $request->get($language);
            }

            $input["name"] = $name;

            $apiToken = config('currency.services.external.api_key');
            $response = Http::get("https://v6.exchangerate-api.com/v6/$apiToken/latest/USD");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['conversion_rates'][$request->get('code')])) {
                    $input['rate'] = $data['conversion_rates'][$request->get('code')];
                } else {
                    $input['rate'] = 0.5;
                }

            }

            $currency = Currency::create($input);

            Log::info('Currency ' . $currency->id . ' successfully created');
            return $currency;
        });
    }

    public function update(Request $request, string $id)
    {

        return DB::transaction(function () use ($request, $id) {
            $currency = $this->show($id);

            $input = $request->only(['code', 'symbol']);

            $languages = $this->languageRepository->allCodes();

            foreach ($languages as $language) {
                $info[$language] = $request->get($language);
            }

            $input["name"] = $info;

            $currency->update($input);

            Log::info('Currency ' . $currency->id . ' successfully updated.');
            return $currency;
        });
    }

    public function destroy(string $id)
    {

        return DB::transaction(function () use ($id) {
            $currency = $this->show($id);

            $currency->delete();

            Log::info('Currency ' . $currency->id . ' successfully deleted.');
            return $currency;
        });
    }

    public function show(string $id)
    {
        return Currency::find($id);
    }

    public function checkCode(Request $request)
    {
        $query = Currency::code($request->get('code'));

        if ($request->get("id")) {
            $query->where('id', '!=', $request->get('id'));
        }

        return $query->exists();
    }

    public function getByCode(string $code)
    {
        return Currency::where("code", $code)->first();
    }

    public function convert(Request $request)
    {
        $fromCurrency = $this->show($request->get('from_id'));
        $toCurrency   = $this->show($request->get('to_id'));

        $amountConverted = Helpers::convertCurrency($request->get('amount'), $fromCurrency, $toCurrency);

        return [
            'fromCode' => $fromCurrency->code,
            'toCode'   => $toCurrency->code,
            'amount'   => $amountConverted,
        ];
    }
}
