<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Profession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfessionController extends Controller
{
    public function index(Request $request): View
    {
        $professions = Profession::query()
            ->withCount('diklatTasks')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.professions.index', compact('professions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:professions,code'],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Profession::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('master.professions.index')->with('success', 'Master Profesi berhasil ditambahkan.');
    }

    public function update(Request $request, Profession $profession): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('professions', 'code')->ignore($profession->id)],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $profession->update([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('master.professions.index')->with('success', 'Master Profesi berhasil diperbarui.');
    }

    public function destroy(Profession $profession): RedirectResponse
    {
        if ($profession->diklatTasks()->exists()) {
            return back()->with('error', 'Profesi tidak dapat dihapus karena masih digunakan pada Tugas Diklat.');
        }

        $profession->delete();

        return redirect()->route('master.professions.index')->with('success', 'Master Profesi berhasil dihapus.');
    }
}
