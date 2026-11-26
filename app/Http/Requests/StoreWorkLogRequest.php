<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWorkLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_active;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isQuickIncidental = $this->boolean('create_incidental_task');
        $isOnHold = $this->input('status_after_log') === TaskStatus::OnHold->value;

        return [
            'create_incidental_task' => ['nullable', 'boolean'],
            'task_id' => [$isQuickIncidental ? 'nullable' : 'required', 'exists:tasks,id'],
            'incidental_title' => [$isQuickIncidental ? 'required' : 'nullable', 'string', 'max:200'],
            'incidental_category_id' => [$isQuickIncidental ? 'required' : 'nullable', 'exists:work_categories,id'],
            'incidental_estimated_hours' => [$isQuickIncidental ? 'required' : 'nullable', 'numeric', 'min:0.25'],
            'log_date' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'output_achieved' => ['nullable', 'integer', 'min:0'],
            'activity_description' => ['required', 'string', 'min:5'],
            'status_after_log' => ['required', Rule::enum(TaskStatus::class)],
            'obstacle' => [$isOnHold ? 'required' : 'nullable', 'string'],
            'solution_plan' => [$isOnHold ? 'required' : 'nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'task_id.required' => 'Silakan pilih tugas yang dikerjakan.',
            'log_date.required' => 'Tanggal pekerjaan wajib diisi.',
            'activity_description.required' => 'Uraian aktivitas pekerjaan wajib diisi.',
            'obstacle.required' => 'Kendala wajib diisi jika status pekerjaan Tertunda.',
            'solution_plan.required' => 'Rencana solusi wajib diisi jika status pekerjaan Tertunda.',
        ];
    }
}
