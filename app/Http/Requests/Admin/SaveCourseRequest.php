<?php

namespace App\Http\Requests\Admin;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $course = $this->route('course');

        if ($course instanceof Course) {
            return Gate::allows('update', $course);
        }

        return Gate::allows('create', Course::class);
    }

    protected function prepareForValidation(): void
    {
        $code = $this->input('code');

        if (is_string($code)) {
            $this->merge(['code' => Str::upper(trim($code))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'bail',
                'required',
                'regex:/^[A-Z]{3}\d{4}$/',
                Rule::unique('courses', 'code')->ignore($this->route('course')),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Informe o código da disciplina.',
            'code.regex'    => 'O código deve ter 3 letras seguidas de 4 dígitos',
            'code.unique'   => 'Este código já está em uso',
            'name.required' => 'Informe o nome da disciplina.',
            'name.max'      => 'O nome deve ter no máximo 255 caracteres.',
        ];
    }
}