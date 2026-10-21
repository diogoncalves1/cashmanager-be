<?php
namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Language\Repositories\LanguageRepository;
use Modules\Notification\DataTables\BroadcastNotificationDataTable;
use Modules\Notification\Http\Requests\BroadcastNotificationRequest;
use Modules\Notification\Mail\BroadcastNotificationCreatedMail;
use Modules\Notification\Repositories\BroadcastNotificationRepository;
use Modules\Notification\Services\BroadcastNotificationService;

class BroadcastNotificationController extends ApiController
{
    public function __construct(protected LanguageRepository $langRepo, protected BroadcastNotificationRepository $repository, protected BroadcastNotificationService $service)
    {
    }

    /**
     * Display a listing of the resource.
     * @param BroadcastNotificationDataTable $dataTable
     *
     */
    public function index(BroadcastNotificationDataTable $dataTable)
    {
        $this->allowedAction('viewNotifications');

        return $dataTable->render('notification::broadcast-notifications.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->allowedAction('createNotifications');

        $languages = $this->langRepo->all();

        return view('notification::broadcast-notifications.create', compact('languages'));
    }

    /**
     * Store a newly created resource in storage.
     * @param BroadcastNotificationRequest $request
     * @return RedirectResponse
     */
    public function store(BroadcastNotificationRequest $request): RedirectResponse
    {
        try {
            $this->allowedAction('createNotifications');

            $this->service->create($request->validated(), Auth::user());

            return redirect()->route('admin.notifications.index')->with('success', 'Notificação global criada com sucesso.');
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->route('admin.notifications.index')->with('error', 'Erro ao criar notificação global.');
        }
    }

    /**
     * Show the mail sent from specified resource.
     * @param string $id
     */
    public function show($id)
    {
        return (new BroadcastNotificationCreatedMail($this->repository->show($id), Auth::user()))->preview();
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     */
    public function edit($id)
    {
        $this->allowedAction('editNotifications');

        $languages    = $this->langRepo->all();
        $notification = $this->repository->show($id);

        return view('notification::broadcast-notifications.create', compact('languages', 'notification'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $this->allowedAction('editNotifications');

            $this->service->update($request->validated(), $id);

            return redirect()->route('admin.notifications.index')->with('success', 'Notificação global atualizada com sucesso.');
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->route('admin.notifications.index')->with('error', 'Erro ao atualizar notificação global.');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            $this->allowedAction('destroyNotifications');

            $this->service->destroy($id);

            return $this->ok(message: 'Tipo de notificação apagado com sucesso.');
        } catch (\Exception $e) {
            Log::error($e);

            return $this->fail('Erro ao apagar tipo de notificação.', $e);
        }
    }
}
