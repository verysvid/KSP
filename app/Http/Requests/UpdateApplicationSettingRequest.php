<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('application-setting.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'system_name' => ['required', 'string', 'max:150'],
            'title_1' => ['required', 'string', 'max:255'],
            'title_2' => ['nullable', 'string', 'max:255'],
            'abbreviation' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'copyright' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'icon' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,ico', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_icon' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'system_name.required' => 'Nama sistem wajib diisi.',
            'title_1.required' => 'Judul 1 wajib diisi.',
            'abbreviation.required' => 'Singkatan wajib diisi.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Format logo harus PNG, JPG, JPEG, atau WEBP.',
            'logo.max' => 'Ukuran logo maksimal 4 MB.',
            'icon.mimes' => 'Format icon harus PNG, JPG, JPEG, WEBP, atau ICO.',
            'icon.max' => 'Ukuran icon maksimal 2 MB.',
        ];
    }
}
