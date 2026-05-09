<?php
namespace Modules\EmailLayout\Http\Controllers;

use App\Http\Controllers\AppController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\EmailLayout\DataTables\EmailLayoutDataTable;
use Modules\EmailLayout\Http\Requests\EmailLayoutRequest;
use Modules\EmailLayout\Repositories\EmailLayoutRepository;
use Modules\EmailLayout\Repositories\EmailTypeRepository;

class EmailLayoutController extends AppController
{
    private $repository;
    private $emailTypeRepository;

    public function __construct(EmailLayoutRepository $repository, EmailTypeRepository $emailTypeRepository)
    {
        $this->repository          = $repository;
        $this->emailTypeRepository = $emailTypeRepository;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(EmailLayoutDataTable $dataTable)
    {
        $this->allowedAction('viewEmailLayout');

        return $dataTable->render('emaillayout::email_layouts.index');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $this->allowedAction('editEmailLayout');
        $emailType = $this->emailTypeRepository->show($id);
        if (! $emailType) {
            abort(404);
        }

        $emailLayout = $this->repository->showByEmailType($id);
        return view('emaillayout::email_layouts.edit',
            ['emailLayout' => $emailLayout, 'emailType' => $emailType]);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(EmailLayoutRequest $request, $id)
    {
        $this->allowedAction('editEmailLayout');
        $this->repository->update($request, $id);

        return redirect()->route('admin.settings.emailLayouts.index');
    }

}
