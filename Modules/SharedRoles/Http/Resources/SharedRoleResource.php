<?php
namespace Modules\SharedRoles\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class SharedRoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user() ? $request->user() : Auth::user();

        $lang = $user->preferences?->lang ?? 'en';

        return [
            'name' => $this->name->$lang,
            'code' => $this->code,
        ];
    }
}
