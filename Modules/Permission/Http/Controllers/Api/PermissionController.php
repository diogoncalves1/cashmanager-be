<?php

namespace Modules\Permission\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Modules\Permission\Repositories\PermissionRepository;

class PermissionController extends ApiController
{
    private PermissionRepository $repository;

    public function __construct(PermissionRepository $repository)
    {
        $this->repository = $repository;
    }
}
