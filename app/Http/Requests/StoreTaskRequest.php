<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ComplexityLevel;
use App\Enums\PriorityLevel;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
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
        return [
            'department' => ['nullable', Rule::in(['it', 'rm'])],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'is_incidental' => ['nullable', 'boolean'],
            'target_id' => ['nullable', 'exists:targets,id'],
            'parent_task_id' => ['nullable', 'exists:tasks,id'],
            'work_category_id' => ['nullable', 'exists:work_categories,id'],
            'pic_id' => ['nullable', 'exists:pics,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0.25', 'max:2000'],
            'estimated_start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:estimated_start_date'],
            'due_date_change_reason' => ['nullable', 'string', 'max:500'],
            'complexity' => ['nullable', Rule::enum(ComplexityLevel::class)],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'estimated_output' => ['nullable', 'integer', 'min:1'],
            'output_unit' => ['nullable', 'string', 'max:60'],
            'priority' => ['required', Rule::enum(PriorityLevel::class)],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul tugas wajib diisi.',
            'due_date.after_or_equal' => 'Tanggal target harus sama atau setelah tanggal mulai.',
        ];
    }
}
