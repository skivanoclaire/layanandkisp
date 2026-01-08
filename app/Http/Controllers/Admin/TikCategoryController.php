<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TikCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TikCategoryController extends Controller
{
    public function index() {
        $items = TikCategory::orderBy('name')->get();
        return view('admin.tik.categories.index', compact('items'));
    }

    public function store(Request $r) {
        $data = $r->validate([
            'name' => 'required|string|max:100|unique:tik_categories,name',
            'code'        => 'required|string|alpha_num|max:10|unique:tik_categories,code',
            'description' => 'nullable|string|max:255',
        ]);
        $data['code'] = Str::upper(trim($data['code']));
        TikCategory::create($data);
        return back()->with('status','Kategori ditambahkan.');
    }

    public function edit(TikCategory $category) {
        return view('admin.tik.categories.edit', compact('category'));
    }

    public function update(Request $r, TikCategory $category) {
        $data = $r->validate([
            'name' => 'required|string|max:100|unique:tik_categories,name,'.$category->id,
            'code'        => 'required|string|alpha_num|max:10|unique:tik_categories,code,'.$category->id,
            'description' => 'nullable|string|max:255',
        ]);
        $data['code'] = Str::upper(trim($data['code']));
        $category->update($data);
        return redirect()->route('admin.tik.categories.index')->with('status','Kategori diperbarui.');
    }

    public function destroy(TikCategory $category) {
        $category->delete();
        return back()->with('status','Kategori dihapus.');
    }
}
