<?php
namespace Modules\Asset\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Modules\Asset\DataTables\AssetsDataTable;
use Modules\Asset\Repositories\AssetRepository;

class AssetController extends ApiController
{
    protected AssetRepository $repository;

    public function __construct(AssetRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(AssetsDataTable $dataTable)
    {
        $this->allowedAction('viewAsset');

        return $dataTable->render('asset::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->allowedAction('createAsset');

        return view('asset::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->allowedAction('createAsset');

        $this->repository->store($request);

        return redirect()->route('admin.assets.index');

    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('asset::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $this->allowedAction('editAsset');

        $asset = $this->repository->show($id);

        return view('asset::edit', compact('asset'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $this->allowedAction('editAsset');

        $this->repository->update($request, $id);

        return redirect()->route('admin.assets.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $this->allowedAction('createAsset');

            $this->repository->destroy($id);

            return $this->ok(message: "Asset apagado com sucesso!");
        } catch (\Exception $e) {
            return $this->fail("Erro ao apagar asset", $e, 500);
        }

    }

}
