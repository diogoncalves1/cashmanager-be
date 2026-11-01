<?php

namespace Modules\Category\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => trim(preg_replace('/\s+/u', ' ', $this->input('name')))]);
        }
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:revenue,expense',
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|in:Car,Utensils,Wallet,ShoppingCart,TrendingDown,TrendingUp,Banknote,Briefcase,ShoppingBasket,ChefHat,Bike,CarTaxiFront,Gamepad2,Gamepad,Home,Building,School,Shirt,Pill,Laptop,Hotel,Map,CircleEllipsis,BookOpen,Lightbulb,Wifi,Dumbbell,HeartPulse,GraduationCap,Plane,CircleDollarSign,Receipt,Gift,Landmark,CreditCard,Fuel,Bus,Tv',
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'user_id' => 'prohibited',
            'is_default' => 'prohibited',
            'code' => 'prohibited',
            'parent_id' => 'prohibited',
            'archived_at' => 'prohibited',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('category::validation.name_required'),
            'name.max' => __('category::validation.name_max'),
            'type.required' => __('category::validation.type'),
            'type.in' => __('category::validation.type'),
            'color.regex' => __('category::validation.color'),
            'icon.in' => __('category::validation.icon'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => __('category::validation.summary'),
            'errors' => $validator->errors(),
        ], 422));
    }
}
