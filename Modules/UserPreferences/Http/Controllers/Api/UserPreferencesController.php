<?php

namespace Modules\UserPreferences\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\UserPreferences\Repositories\UserPreferencesRepository;
use Modules\UserPreferences\Http\Requests\UserPreferencesRequest;
use Modules\UserPreferences\Http\Resources\UserPreferecesResource;

class UserPreferencesController extends ApiController
{
    protected $repository;

    public function __construct(UserPreferencesRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Update the specified resource in storage.
     * @param UserPreferencesRequest $request
     * @return JsonResponse
     */
    public function update(UserPreferencesRequest $request): JsonResponse
    {
        try {
            $userPreferences = $this->repository->update($request);

            return $this->ok(new UserPreferecesResource($userPreferences), __('userpreferences::messages.user-preferences.update'));
        } catch (\Exception $e) {
            Log::error($e);
            return $this->fail(__('userpreferences::messages.user-preferences.errors.update'), $e, 500);
        }
    }
}
