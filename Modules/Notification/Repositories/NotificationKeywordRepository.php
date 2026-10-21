<?php
namespace Modules\Notification\Repositories;

use App\Repositories\FilesRepository;
use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Notification\Entities\NotificationKeyword;

class NotificationKeywordRepository extends FilesRepository implements RepositoryInterface
{
    public function all()
    {
        return NotificationKeyword::all();
    }

    public function store(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $input = $request->only(['keyword', 'description']);

                $notificationKeyword = NotificationKeyword::create($input);

                return $notificationKeyword;
            });
        } catch (\Exception $e) {
            throw new \Exception('Error creating notification keyword: ' . $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            $notificationKeyword = $this->show($id);

            $input = $request->only(['keyword', 'description']);

            $notificationKeyword->update($input);

            return $notificationKeyword;
        });
    }

    public function show(string $id)
    {
        return NotificationKeyword::findOrFail($id);
    }

    public function destroy(string $id)
    {
        return DB::transaction(function () use ($id) {
            $notificationKeyword = $this->show($id);

            $notificationKeyword->delete();

            Log::info("Deleted notification keyword with ID: {$id}");

            return $notificationKeyword;
        });
    }

}
