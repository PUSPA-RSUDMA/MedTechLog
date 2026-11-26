<?php

declare(strict_types=1);

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\WorkCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = WorkCategory::query()
            ->withCount(['targets', 'tasks'])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('master.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:work_categories,code'],
            'name' => ['required', 'string', 'max:150'],
            'color' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        WorkCategory::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('master.categories.index')->with('success', 'Kategori pekerjaan berhasil ditambahkan.');
    }

    public function update(Request $request, WorkCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('work_categories', 'code')->ignore($category->id)],
            'name' => ['required', 'string', 'max:150'],
            'color' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'code' => strtoupper($data['code']),
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('master.categories.index')->with('success', 'Kategori pekerjaan berhasil diperbarui.');
    }

    public function destroy(WorkCategory $category): RedirectResponse
    {
        if ($category->tasks()->exists() || $category->targets()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih digunakan pada target/tugas.');
        }

        $category->delete();

        return redirect()->route('master.categories.index')->with('success', 'Kategori pekerjaan berhasil dihapus.');
    }
}
