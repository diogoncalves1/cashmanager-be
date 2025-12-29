<?php
namespace Modules\Asset\Repositories;

use App\Repositories\FilesRepository;
use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Modules\Asset\Entities\Asset;
use Modules\Asset\Entities\AssetSearchView;
use Nwidart\Modules\Facades\Module;

class AssetRepository extends FilesRepository implements RepositoryInterface
{
    public function all()
    {
        return Asset::all();
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $ticker = $request->get('ticker');

                if ($this->showByTicker($ticker)) {
                    throw new \Exception('Ticker ja existe', 500);
                }

                $modulePath = Module::getModulePath('Asset');
                $scriptPath = $modulePath . '/Scripts/finance_fetcher.py';

                $output    = null;
                $returnVar = null;

                exec("python3 $scriptPath $ticker", $output, $returnVar);

                if ($returnVar !== 0) {
                    throw new \Exception('Error on python script', 500);
                }

                $outputStr = implode("\n", $output);

                $data = json_decode($outputStr, true);

                $jsons = ['price_json', 'fundamentals_json', 'dividends_json', 'dividends_history_json', 'price_last_days_history_json', 'news_json'];

                foreach ($jsons as $json) {
                    $data[$json] = json_decode($data[$json], true);
                }

                $asset = Asset::create($data);

                Session::flash('success', 'Asset adicionado com sucesso.');

                return $asset;
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao adicionar Asset');
        }

    }

    public function update(Request $request, string $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $asset = $this->show($id);

                $input["custom_fields"] = $request->only(['dividendDate', 'dividendYield', 'exDividendDate', 'category', 'payoutFrequency', 'marketCap']);

                $basePath = 'uploads/assets/';

                if ($request->hasFile('logo')) {
                    if ($asset->logo != null) {
                        $this->imageDelete($asset->logo);
                    }

                    $imgName       = Str::uuid();
                    $input['logo'] = config('app.url') . $basePath . $this->imageUpload($basePath, $request->file('logo'), $imgName);
                }

                $asset->update($input);

                Session::flash('success', 'Asset editado com sucesso.');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('warning', 'Não foi possível editar asset.');
        }
    }

    public function destroy(string $id)
    {
        return DB::transaction(function () use ($id) {
            $asset = $this->show($id);

            $asset->delete();

            return $asset;
        });
    }

    public function show(string $id)
    {
        return Asset::findOrFail($id);
    }

    public function showByTicker(string $ticker)
    {
        return Asset::where('ticker', $ticker)->first();
    }

    public function search(Request $request)
    {
        $search = $request->get('search');

        $normalized = strtolower(str_replace(['-', ' '], '', $search));

        return AssetSearchView::where(function ($q) use ($normalized) {
            $q->whereRaw("REPLACE(REPLACE(LOWER(name), '-', ''), ' ', '') LIKE ?", ["%$normalized%"])
                ->orWhereRaw("REPLACE(REPLACE(LOWER(ticker), '-', ''), ' ', '') LIKE ?", ["%$normalized%"]);
        })->get();
    }

}
