<?php

namespace Modules\Currency\Http\Controllers;

use Modules\Currency\Repositories\CurrencyRepository;
use App\Http\Controllers\AppController;
use Illuminate\Contracts\Support\Renderable;
use Modules\Currency\DataTables\CurrencyDataTable;

class CurrencyController extends AppController
{
    private CurrencyRepository $currencyRepository;

    public function __construct(CurrencyRepository $currencyRepository)
    {
        $this->currencyRepository = $currencyRepository;
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

        $languages = config('languages');

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

        $currency = $this->currencyRepository->show($id);
        $languages = config('languages');

        return view('currency::create', compact('currency', 'languages'));
    }
}
