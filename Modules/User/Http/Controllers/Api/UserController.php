<?php

namespace Modules\User\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Modules\User\Repositories\UserRepository;

class UserController extends ApiController
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }
}
