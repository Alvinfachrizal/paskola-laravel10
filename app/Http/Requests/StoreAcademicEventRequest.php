<?php

namespace App\Http\Requests;

use App\Models\EventCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy check dilakukan di controller — di sini izinkan semua yang sudah login
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'category_id'  => ['required', 'uuid', Rule::exists('event_categories', 'id')],
            'class_id'     => ['nullable', 'uuid', Rule::exists('school_classes', 'id')],
            'title'        => ['required', 'string', 'max:255'],
            'start_date'   => ['required', 'date'],
            'end_date'     => ['required', 'date', 'after_or_equal:start_date'],
            'description'  => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Validasi tambahan setelah rules dasar lolos:
     * Guru TIDAK BOLEH memilih kategori yang is_holiday = true
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! auth()->user()->hasRole(['Super Admin', 'Admin', 'Kepala Sekolah'])) {
                $categoryId = $this->input('category_id');
                if ($categoryId) {
                    $category = EventCategory::find($categoryId);
                    if ($category && $category->is_holiday) {
                        $validator->errors()->add(
                            'category_id',
                            'Anda tidak memiliki izin untuk membuat event dengan kategori hari libur.'
                        );
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori event wajib dipilih.',
            'title.required'       => 'Judul event wajib diisi.',
            'start_date.required'  => 'Tanggal mulai wajib diisi.',
            'end_date.required'    => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }
}
