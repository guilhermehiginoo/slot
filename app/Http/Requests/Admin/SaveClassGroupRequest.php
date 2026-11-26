<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use App\Models\ClassGroup;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveClassGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        $classGroup = $this->editedClassGroup();

        if ($classGroup) {
            return Gate::allows('update', $classGroup);
        }

        return Gate::allows('create', ClassGroup::class);
    }

    private function editedClassGroup(): ?ClassGroup
    {
        $classGroup = $this->route('classGroup');

        return $classGroup instanceof ClassGroup ? $classGroup : null;
    }

    protected function prepareForValidation(): void
    {
        $number = $this->input('number');

        if (is_string($number)) {
            $this->merge(['number' => Str::upper(trim($number))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'course_id' => [
                'required',
                Rule::exists('courses', 'id')->where(fn (Builder $query) => $query
                    ->whereNull('archived_at')
                    ->when($this->editedClassGroup(), fn (Builder $query, ClassGroup $classGroup) => $query->orWhere('id', $classGroup->course_id))),
            ],
            'semester' => ['required', 'regex:/^\d{4}\.[12]$/'],
            'number'   => [
                'bail',
                'required',
                'regex:/^T\d{2}$/',
                Rule::unique('class_groups', 'number')
                    ->where('course_id', $this->input('course_id'))
                    ->where('semester', $this->input('semester'))
                    ->ignore($this->editedClassGroup()),
            ],
            'professors'   => ['required', 'array', 'min:1'],
            'professors.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where('role', Role::Professor->value),
            ],
            'schedule' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_id.required'  => 'Selecione a disciplina.',
            'semester.required'   => 'Informe o semestre.',
            'semester.regex'      => 'O semestre deve estar no formato ano.1 ou ano.2',
            'number.required'     => 'Informe o número da turma.',
            'number.regex'        => 'O número da turma deve ser T seguido de 2 dígitos',
            'number.unique'       => 'Esta turma já existe neste semestre',
            'professors.required' => 'Selecione ao menos um professor',
            'professors.min'      => 'Selecione ao menos um professor',
            'schedule.required'   => 'Informe o código de horário.',
            'schedule.max'        => 'O código de horário deve ter no máximo 50 caracteres.',
        ];
    }
}
