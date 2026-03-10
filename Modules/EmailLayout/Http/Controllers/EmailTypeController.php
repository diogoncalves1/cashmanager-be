<?php

namespace Modules\EmailLayout\Http\Controllers;

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Session;
use Modules\EmailLayout\Http\Requests\EmailTypeRequest;
use Modules\EmailLayout\Repositories\EmailTypeRepository;

class EmailTypeController extends AppController
{
    private $repository;

    public function __construct(EmailTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->allowedAction('superAdmin');

        $emailTypes = $this->repository->all();

        return view('emaillayout::email_types.index', ['emailTypes' => $emailTypes]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->allowedAction('superAdmin');

        return view('emaillayout::email_types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EmailTypeRequest $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(EmailTypeRequest $request)
    {
        $this->allowedAction('superAdmin');

        $this->repository->store($request);

        return redirect()->route('admin.emailTypes.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($id)
    {
        $this->allowedAction('superAdmin');

        $emailType = $this->repository->show($id);

        if (!$emailType) abort(404);

        return view('emaillayout::email_types.create', ['emailType' => $emailType]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param EmailTypeRequest $request
     * @param $id
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(EmailTypeRequest $request, $id)
    {
        $this->allowedAction('superAdmin');


        $this->repository->update($request, $id);

        return redirect()->route('admin.emailTypes.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        $this->allowedAction('superAdmin');

        $emailType = $this->repository->show($id);

        if (!$emailType)
            abort(404);

        $this->repository->destroy($id);

        return redirect()->route('admin.emailTypes.index');
    }
}
