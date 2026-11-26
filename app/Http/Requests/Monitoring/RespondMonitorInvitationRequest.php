<?php

namespace App\Http\Requests\Monitoring;

use App\Enums\MonitorInvitationStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondMonitorInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('respond', $this->route('monitorInvitation'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(MonitorInvitationStatus::class)->only([
                MonitorInvitationStatus::Accepted,
                MonitorInvitationStatus::Declined,
            ])],
        ];
    }
}
