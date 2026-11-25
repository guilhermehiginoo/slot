<?php

namespace App\Http\Requests\Monitoring;

use App\Models\Slot;
use Illuminate\Foundation\Http\FormRequest;

class SaveSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        $slot = $this->route('slot');

        return $slot === null
            ? $this->user()->can('create', [Slot::class, $this->route('classGroup')])
            : $this->user()->can('update', $slot);
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ends_at'   => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'location'  => ['required', 'string', 'max:255'],
            'capacity'  => ['required', 'integer', 'min:1', 'max:2147483647'],
            'weeks'     => $this->isMethod('post') ? ['sometimes', 'required', 'integer', 'min:1', 'max:52'] : ['prohibited'],
        ];
    }
}
