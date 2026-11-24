<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Changing the sign-in identifier is a sensitive action: it must
            // not be possible from a borrowed, still-signed-in browser.
            'current_password' => $this->emailChanged()
                ? ['required', 'current_password']
                : ['nullable', 'current_password'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Masukkan kata sandi saat ini untuk mengganti email.',
            'current_password.current_password' => 'Kata sandi saat ini tidak cocok.',
        ];
    }

    private function emailChanged(): bool
    {
        $email = $this->input('email');

        return is_string($email)
            && strcasecmp(trim($email), (string) $this->user()->email) !== 0;
    }
}
