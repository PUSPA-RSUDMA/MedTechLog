<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Pic;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PicController extends Controller
{
    public function index(Request $request): View
    {
        $pics = Pic::query()
            ->with('unit')
            ->withCount('tasks')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.pics.index', [
            'pics' => $pics,
            'units' => Unit::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'unit_id' => ['required', 'exists:units,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Pic::create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?: null,
            'unit_id' => $data['unit_id'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('master.pics.index')->with('success', 'Master PIC berhasil ditambahkan.');
    }

    public function update(Request $request, Pic $pic): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'unit_id' => ['required', 'exists:units,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $pic->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?: null,
            'unit_id' => $data['unit_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('master.pics.index')->with('success', 'Data PIC berhasil diperbarui.');
    }

    public function destroy(Pic $pic): RedirectResponse
    {
        if ($pic->tasks()->exists()) {
            $pic->update(['is_active' => false]);

            return back()->with('success', 'PIC memiliki riwayat tugas sehingga dinonaktifkan.');
        }

        $pic->delete();

        return redirect()->route('master.pics.index')->with('success', 'PIC berhasil dihapus.');
    }
}
