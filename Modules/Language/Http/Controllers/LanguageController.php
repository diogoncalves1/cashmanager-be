<?php
namespace Modules\Language\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Contracts\Support\Renderable;
use Modules\Language\DataTables\LanguageDataTable;
use Modules\Language\Http\Requests\LanguageRequest;
use Modules\Language\Repositories\LanguageRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LanguageController extends ApiController
{
    protected $repository;

    public function __construct(LanguageRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     * @param LanguageDataTable $dataTable
     * @throws AuthenticationException
     */
    public function index(LanguageDataTable $dataTable)
    {
        $this->allowedAction('superAdmin');

        return $dataTable->render('language::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     * @throws AuthenticationException
     */
    public function create(): Renderable
    {
        $this->allowedAction('superAdmin');

        return view('language::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param LanguageRequest $request
     * @return RedirectResponse
     * @throws AuthenticationException
     */
    public function store(LanguageRequest $request): RedirectResponse
    {
        $this->allowedAction('superAdmin');

        $this->repository->store($request);

        return redirect()->route('admin.languages.index');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Renderable
     * @throws AuthenticationException
     */
    public function edit($id): Renderable
    {
        $this->allowedAction('superAdmin');

        $language = $this->repository->show($id);

        return view('language::create', compact('language'));
    }

    /**
     * Update the specified resource in storage.
     * @param LanguageRequest $request
     * @param string $id
     * @return RedirectResponse
     * @throws AuthenticationException
     */
    public function update(LanguageRequest $request, string $id): RedirectResponse
    {
        $this->allowedAction('superAdmin');

        $this->repository->update($request, $id);

        return redirect()->route('admin.languages.index');
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->allowedAction('destroyUser');

            $this->repository->destroy($id);

            return $this->ok(message: "Idioma apagado com sucesso!");
        } catch (\Exception $e) {
            return $this->fail("Erro ao apagar idioma.", $e, 500);
        }
    }
}
