<?php
namespace Modules\Language\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class LanguageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user() ? $request->user() : Auth::user();

        $lang = $user->preferences?->lang ?? 'en';

        return [
            'id'   => (string) $this->id,
            'code' => $this->code,
            'name' => $this->name->$lang,
        ];
    }
}
