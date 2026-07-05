<?php

namespace App\Http\Requests\AuthRequests\RegistersRequest;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterCompanyCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // بيانات المستخدم
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'phone'     => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'nullable|boolean',
            'image_url' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            // بيانات الشركة
            'company_name'    => ['required', 'string', 'max:255'],
            'company_name_ar' => ['required', 'string', 'max:255'],
            'commercial_reg'  => ['required', 'string', 'unique:companies,commercial_reg'],
            'tax_number'      => ['required', 'string', 'unique:companies,tax_number'],
            'company_address' => ['required', 'string'],
        ];
    }
}
