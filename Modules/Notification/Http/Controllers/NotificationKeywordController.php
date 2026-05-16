<?php
namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Modules\Notification\DataTables\NotificationKeywordDataTable;
use Modules\Notification\Http\Requests\NotificationKeywordRequest;
use Modules\Notification\Repositories\NotificationKeywordRepository;

class NotificationKeywordController extends ApiController
{
    public function __construct(protected NotificationKeywordRepository $repository)
    {
    }

    /**
     * Display a listing of the resource.
     * @param NotificationKeywordDataTable $dataTable
     * @return View
     */
    public function index(NotificationKeywordDataTable $dataTable)
    {
        $this->allowedAction('superAdmin');

        return $dataTable->render('notification::notification-keywords.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return View
     */
    public function create()
    {
        $this->allowedAction('superAdmin');

        return view('notification::notification-keywords.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param NotificationKeywordRequest
     * @return RedirectResponse
     */
    public function store(NotificationKeywordRequest $request)
    {
        try {
            $this->allowedAction('superAdmin');

            $this->repository->store($request);

            return redirect()->route('admin.notificationKeywords.index')->with('success', 'Palavra-chave de notificação criada com sucesso.');
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->route('admin.notifications.index')->with('error', 'Erro ao criar palavra-chave de notificação.');
        }
    }

    /**
     * Show the specified resource.
     */
    // public function show($id)
    // {
    //     return view('notification::show');
    // }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return View
     */
    public function edit(string $id)
    {
        $this->allowedAction('superAdmin');

        $keyword = $this->repository->show($id);

        return view('notification::notification-keywords.create', compact('keyword'));
    }

    /**
     * Update the specified resource in storage.
     * @param NotificationKeywordRequest
     * @param string $id
     * @return RedirectResponse
     */
    public function update(NotificationKeywordRequest $request, string $id)
    {
        try {
            $this->allowedAction('superAdmin');

            $this->repository->update($request, $id);

            return redirect()->route('admin.notificationKeywords.index')->with('success', 'Palavra-chave de notificação atualizada com sucesso.');
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->route('admin.notifications.index')->with('error', 'Erro ao atualizar palavra-chave de notificação.');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     */
    public function destroy(string $id)
    {
        try {
            $this->allowedAction('superAdmin');

            $this->repository->destroy($id);

            return $this->ok(message: 'Palavra-chave de notificação apagada com sucesso');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao apagar palavra-chave de notificação', $e);
        }
    }
}
