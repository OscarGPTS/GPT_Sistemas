<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\EquipmentRequest;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseDocument;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            $monthDate = now()->startOfMonth();
        }

        $query = Expense::with('category', 'creator');
        if ($request->input('all') !== '1') {
            $query->whereBetween('expense_date', [$monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()]);
        }
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('concept', 'like', "%$search%")
                    ->orWhere('supplier', 'like', "%$search%")
                    ->orWhere('code', 'like', "%$search%");
            });
        }

        $expenses = $query->latest('expense_date')->paginate(20)->withQueryString();

        // KPIs for current period
        $monthlyTotal = (float) Expense::whereBetween('expense_date', [$monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()])->sum('amount');
        $monthlyByCategory = Expense::whereBetween('expense_date', [$monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(fn ($r) => ['category' => $r->category, 'total' => (float) $r->total]);
        $recurringTotal = (float) Expense::whereBetween('expense_date', [$monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()])
            ->where('type', 'recurring')->sum('amount');
        $variableTotal = (float) Expense::whereBetween('expense_date', [$monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()])
            ->where('type', 'variable')->sum('amount');

        // Trend last 6 months
        $trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->copy()->startOfMonth()->subMonths($i);
            $total = (float) Expense::whereBetween('expense_date', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->sum('amount');
            $trend[] = ['label' => $m->locale('es')->isoFormat('MMM YY'), 'value' => $total];
        }

        return view('expenses.index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'filters' => array_merge(['month' => $month], $request->only(['category_id', 'type', 'q', 'all'])),
            'monthDate' => $monthDate,
            'monthlyTotal' => $monthlyTotal,
            'monthlyByCategory' => $monthlyByCategory,
            'recurringTotal' => $recurringTotal,
            'variableTotal' => $variableTotal,
            'trend' => $trend,
        ]);
    }

    public function create(Request $request): View
    {
        return view('expenses.form', [
            'expense' => new Expense([
                'type' => 'variable',
                'expense_date' => now()->toDateString(),
                'payment_method' => 'transfer',
            ]),
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'assets' => Asset::orderBy('internal_code')->get(),
            'equipmentRequests' => EquipmentRequest::whereIn('status', ['purchased', 'delivered'])->orderBy('code', 'desc')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateExpense($request);
        $data['created_by'] = auth()->id();
        $expense = Expense::create($data);

        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $this->storeDocument($expense, $file);
            }
        }

        return redirect()->route('expenses.show', $expense)->with('success', 'Gasto registrado.');
    }

    public function show(Expense $expense): View
    {
        $expense->load(['category', 'creator', 'documents.uploader', 'project', 'asset', 'equipmentRequest']);
        return view('expenses.show', ['expense' => $expense]);
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.form', [
            'expense' => $expense,
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'assets' => Asset::orderBy('internal_code')->get(),
            'equipmentRequests' => EquipmentRequest::whereIn('status', ['purchased', 'delivered'])->orderBy('code', 'desc')->get(),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $data = $this->validateExpense($request);
        $expense->update($data);
        if ($request->hasFile('receipts')) {
            foreach ($request->file('receipts') as $file) {
                $this->storeDocument($expense, $file);
            }
        }
        return redirect()->route('expenses.show', $expense)->with('success', 'Gasto actualizado.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Gasto eliminado.');
    }

    public function downloadDocument(int $id)
    {
        $doc = ExpenseDocument::findOrFail($id);
        return Storage::download($doc->path, $doc->original_name);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $month = $request->input('month', now()->format('Y-m'));
        try {
            $monthDate = Carbon::createFromFormat('Y-m', $month);
        } catch (\Throwable) {
            $monthDate = now();
        }
        $filename = 'gastos_'.$monthDate->format('Y-m').'.csv';

        return response()->streamDownload(function () use ($monthDate) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Código', 'Fecha', 'Categoría', 'Tipo', 'Concepto', 'Monto', 'Proveedor', 'Factura', 'Método', 'Notas']);
            Expense::with('category')
                ->whereBetween('expense_date', [$monthDate->copy()->startOfMonth(), $monthDate->copy()->endOfMonth()])
                ->orderBy('expense_date')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $e) {
                        fputcsv($out, [
                            $e->code,
                            $e->expense_date?->format('Y-m-d'),
                            $e->category?->name,
                            $e->type === 'recurring' ? 'Recurrente' : 'Variable',
                            $e->concept,
                            $e->amount,
                            $e->supplier,
                            $e->invoice_number,
                            $e->payment_method,
                            $e->notes,
                        ]);
                    }
                });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function storeDocument(Expense $expense, $file): ExpenseDocument
    {
        $path = $file->store('expenses/'.$expense->id);
        return $expense->documents()->create([
            'uploaded_by' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:expense_categories,id'],
            'type' => ['required', 'in:recurring,variable'],
            'concept' => ['required', 'string', 'max:200'],
            'notes' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'expense_date' => ['required', 'date'],
            'supplier' => ['nullable', 'string', 'max:200'],
            'invoice_number' => ['nullable', 'string', 'max:80'],
            'payment_method' => ['required', 'in:cash,transfer,card,check,other'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'asset_id' => ['nullable', 'exists:assets,id'],
            'equipment_request_id' => ['nullable', 'exists:equipment_requests,id'],
            'receipts.*' => ['nullable', 'file', 'max:10240'],
        ]);
    }
}
