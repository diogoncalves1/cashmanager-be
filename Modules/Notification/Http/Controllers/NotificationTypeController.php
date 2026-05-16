<?php
namespace Modules\Notification\Http\Controllers;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Language\Repositories\LanguageRepository;
use Modules\Notification\DataTables\NotificationTypeDataTable;
use Modules\Notification\Http\Requests\NotificationTypeRequest;
use Modules\Notification\Mail\NotificationTypeMail;
use Modules\Notification\Repositories\NotificationKeywordRepository;
use Modules\Notification\Repositories\NotificationTypeRepository;
use Modules\Notification\Services\NotificationTypeService;

class NotificationTypeController extends ApiController
{

    public function __construct(protected NotificationTypeRepository $repository, protected NotificationKeywordRepository $keywordRepository, protected NotificationTypeService $service, protected LanguageRepository $languageRepository)
    {
    }

    /**
     * Display a listing of the resource.
     * @param NotificationTypeDataTable $dataTable
     * @return \Illuminate\Contracts\View\View
     */
    public function index(NotificationTypeDataTable $dataTable)
    {
        $this->allowedAction('viewNotificationTypes');

        return $dataTable->render('notification::notification-types.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->allowedAction('createNotificationTypes');

        $keywordsList = $this->keywordRepository->all();
        $languages    = $this->languageRepository->all();

        return view('notification::notification-types.create', compact('keywordsList', 'languages'));
    }

    /**
     * Store a newly created resource in storage.
     * @param NotificationTypeRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(NotificationTypeRequest $request)
    {
        try {
            $this->allowedAction('createNotificationTypes');

            $this->service->create($request->validated());

            return redirect()->route('admin.notificationTypes.index')->with('success', 'Tipo de notificação criado com sucesso.');
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->route('admin.notificationTypes.index')->with('error', 'Erro ao criar tipo de notificação.');
        }
    }

    /**
     * Show the mail sent from specified resource.
     * @param string $id
     */
    public function show(string $id)
    {
        return (new NotificationTypeMail($this->repository->show($id), Auth::user()))->preview();
    }

    /**
     * Show the form for editing the specified resource.
     * @param string $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(string $id)
    {
        $this->allowedAction('editNotificationTypes');

        $type           = $this->repository->show($id);
        $languages      = $this->languageRepository->all();
        $keywordsList   = $this->keywordRepository->all();
        $typeKeywordIds = $type->keywords()->get()->pluck('id')->toArray();

        return view('notification::notification-types.create', compact('type', 'languages', 'keywordsList', 'typeKeywordIds'));
    }

    /**
     * Update the specified resource in storage.
     * @param NotificationTypeRequest $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(NotificationTypeRequest $request, string $id)
    {
        try {
            $this->allowedAction('editNotificationTypes');

            $this->service->update($request->validated(), $id);

            return redirect()->route('admin.notificationTypes.index')->with('success', 'Tipo de notificação atualizado com sucesso.');
        } catch (\Exception $e) {
            Log::error($e);

            return redirect()->route('admin.notificationTypes.index')->with('error', 'Erro ao atualizar tipo de notificação.');
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $this->allowedAction('destroyNotificationTypes');

            $this->repository->destroy($id);

            return $this->ok(message: 'Tipo de notificação apagado com sucesso');
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail('Erro ao apagar tipo de notificação', $e);
        }
    }

    /**
     * Check if a code is available.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkCode(Request $request)
    {
        try {
            $exists = $this->repository->checkIfCodeExists($request->get('code'), $request->get('id'));

            return $this->ok(['is_available' => ! $exists]);
        } catch (\Exception $e) {
            return $this->fail('Erro ao verificar código', $e);
        }
    }
}
