<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\AppController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Category\Repositories\CategoryRepository;
use Modules\Currency\Repositories\CurrencyRepository;
use Modules\User\Repositories\UserRepository;

class HomeController extends AppController
{
    protected $userRepo;
    protected $categoryRepo;
    protected $currencyRepo;

    public function __construct(UserRepository $userRepo, CategoryRepository $categoryRepo, CurrencyRepository $currencyRepo)
    {
        $this->userRepo = $userRepo;
        $this->categoryRepo = $categoryRepo;
        $this->currencyRepo = $currencyRepo;
    }

    public function index()
    {
        $this->allowedAction('viewAdmin');

        $totalUsers = $this->userRepo->all()->count();
        $totalCategories = $this->categoryRepo->all()->count();
        $totalCurrencies = $this->currencyRepo->all()->count();
        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>=', time() - 300)
            ->count();


        return view('admin.index', compact('totalUsers', 'totalCategories', 'totalCurrencies', 'activeSessions'));
    }
}
