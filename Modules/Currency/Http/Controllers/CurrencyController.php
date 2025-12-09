<?php
namespace Modules\Currency\Http\Controllers;

use App\Http\Controllers\AppController;
use Illuminate\Contracts\Support\Renderable;
use Modules\Currency\DataTables\CurrencyDataTable;
use Modules\Currency\Repositories\CurrencyRepository;
use Modules\Language\Repositories\LanguageRepository;

class CurrencyController extends AppController
{
    private CurrencyRepository $repository;
    private LanguageRepository $languageRepository;

    public function __construct(CurrencyRepository $repository, LanguageRepository $languageRepository)
    {
        $this->repository         = $repository;
        $this->languageRepository = $languageRepository;
    }

    /**
     * Display a listing of the resource.
     * @throws AuthenticationException
     */
    public function index(CurrencyDataTable $dataTable)
    {
        $this->allowedAction('viewCurrency');

        return $dataTable->render('currency::index');
    }

    /**
     * Show the form for create a new resource.
     * @return Renderable
     * @throws AuthorizationException
     */
    public function create(): Renderable
    {
        $this->allowedAction('createCurrency');

        $languages = $this->languageRepository->all();

        return view('currency::create', compact('languages'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return Renderable
     * @throws AuthorizationException
     */
    public function edit(string $id): Renderable
    {
        $this->allowedAction('editCurrency');

        $currency  = $this->repository->show($id);
        $languages = $this->languageRepository->all();

        return view('currency::create', compact('currency', 'languages'));
    }
}
