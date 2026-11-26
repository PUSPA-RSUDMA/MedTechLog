<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Enums\PeriodType;
use App\Http\Controllers\Controller;
use App\Models\WorkPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkPeriodController extends Controller
{
    public function index(): View
    {
        $periods = WorkPeriod::query()
            ->withCount('targets')
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('master.periods.index', [
            'periods' => $periods,
            'types' => PeriodType::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::enum(PeriodType::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        WorkPeriod::create([
            ...$data,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('master.periods.index')->with('success', 'Periode kerja berhasil ditambahkan.');
    }

    public function update(Request $request, WorkPeriod $period): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::enum(PeriodType::class)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $period->update([
            ...$data,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('master.periods.index')->with('success', 'Periode kerja berhasil diperbarui.');
    }

    public function destroy(WorkPeriod $period): RedirectResponse
    {
        if ($period->targets()->exists()) {
            return back()->with('error', 'Periode tidak dapat dihapus karena masih memiliki target.');
        }

        $period->delete();

        return redirect()->route('master.periods.index')->with('success', 'Periode kerja berhasil dihapus.');
    }
}
