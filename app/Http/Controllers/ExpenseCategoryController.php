<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        return view('expense_categories.index', [
            'categories' => ExpenseCategory::withCount('expenses')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:expense_categories,name'],
            'color' => ['nullable', 'string', 'max:16'],
            'monthly_budget' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        ExpenseCategory::create($data);
        return back()->with('success', 'Categoría creada.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:expense_categories,name,'.$expenseCategory->id],
            'color' => ['nullable', 'string', 'max:16'],
            'monthly_budget' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable'],
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $expenseCategory->update($data);
        return back()->with('success', 'Categoría actualizada.');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->expenses()->count() > 0) {
            return back()->withErrors(['category' => 'No se puede eliminar: tiene gastos asociados.']);
        }
        $expenseCategory->delete();
        return back()->with('success', 'Categoría eliminada.');
    }
}
