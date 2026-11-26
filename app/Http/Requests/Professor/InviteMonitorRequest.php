<?php

namespace App\Http\Requests\Professor;

use App\Enums\Role;
use App\Models\{ClassGroup, User};
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\{Rule, Validator};

class InviteMonitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('manageMonitors', $this->classGroup());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', Role::Student->value)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'Só alunos podem ser convidados para a monitoria.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $student    = User::select(['id', 'name'])->findOrFail($this->integer('user_id'));
                $classGroup = $this->classGroup();

                if ($classGroup->monitors()->whereKey($student->id)->exists()) {
                    $validator->errors()->add('user_id', "O {$student->name} já é monitor desta turma.");
                } elseif ($classGroup->monitorInvitations()->pending()->where('user_id', $student->id)->exists()) {
                    $validator->errors()->add('user_id', "O {$student->name} já tem um convite pendente para esta turma.");
                }
            },
        ];
    }

    public function classGroup(): ClassGroup
    {
        /** @var ClassGroup */
        return $this->route('classGroup');
    }
}
