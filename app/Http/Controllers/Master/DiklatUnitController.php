<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\DiklatUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiklatUnitController extends Controller
{
    public function index(Request $request): View
    {
        $diklatUnits = DiklatUnit::query()
            ->withCount('diklatTasks')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%')->orWhere('code', 'like', '%' . $request->string('search') . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.diklat-units.index', compact('diklatUnits'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:diklat_units,code'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DiklatUnit::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('master.diklat-units.index')->with('success', 'Master Unit Diklat berhasil ditambahkan.');
    }

    public function update(Request $request, DiklatUnit $diklatUnit): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('diklat_units', 'code')->ignore($diklatUnit->id)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $diklatUnit->update([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('master.diklat-units.index')->with('success', 'Master Unit Diklat berhasil diperbarui.');
    }

    public function destroy(DiklatUnit $diklatUnit): RedirectResponse
    {
        if ($diklatUnit->diklatTasks()->exists()) {
            return back()->with('error', 'Unit Diklat tidak dapat dihapus karena masih digunakan pada Tugas Diklat.');
        }

        $diklatUnit->delete();

        return redirect()->route('master.diklat-units.index')->with('success', 'Master Unit Diklat berhasil dihapus.');
    }
}
