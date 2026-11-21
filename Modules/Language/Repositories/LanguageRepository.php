<?php

namespace Modules\Language\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Language\Entities\Language;

class LanguageRepository implements RepositoryInterface
{
    public function all()
    {
        return Language::all();
    }

    public function allCodes()
    {
        return Cache::remember('language_codes',
            now()->addMinutes(60),
            function () {
                return $this->all()->pluck('code')->toArray();
            });
    }

    public function store(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $input = $request->only(['name', 'code']);

                Language::create($input);
                Cache::forget('language_codes');

                Session::flash('success', 'Idioma adicionado com sucesso.');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao adicionar idioma.');
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                $language = $this->show($id);

                $input = $request->all();

                $language->update($input);
                Cache::forget('language_codes');

                Session::flash('success', 'Idioma atualizado com sucesso.');
            });
        } catch (\Exception $e) {
            Log::error($e);
            Session::flash('error', 'Erro ao atualizar idioma.');
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $language = $this->show($id);

                $language->delete();
                Cache::forget('language_codes');
            });

        } catch (\Exception $e) {
            Log::error($e);
        }
    }

    public function show(string $id)
    {
        return Language::findOrFail($id);
    }
}
