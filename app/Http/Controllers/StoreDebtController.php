<?php

namespace App\Http\Controllers;

use App\Models\StoreDebtMovement;
use App\Models\StoreDebtor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StoreDebtController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $debtorsQuery = StoreDebtor::with(['movements' => fn($query) => $query
            ->orderByDesc('movement_date')->orderByDesc('id')])
            ->orderBy('name');

        if ($search !== '') {
            $debtorsQuery->where('name', 'like', '%' . $search . '%');
        }

        $debtors = $debtorsQuery->get();
        $totals = StoreDebtMovement::query()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'charge' THEN amount ELSE -amount END), 0) as total_owed")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END), 0) as total_paid")
            ->first();

        return view('store-debts.index', [
            'debtors' => $debtors,
            'search' => $search,
            'totalOwed' => (float) $totals->total_owed,
            'totalPaid' => (float) $totals->total_paid,
        ]);
    }

    public function storeDebtor(Request $request): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:255']]);
        StoreDebtor::create($validated);

        return redirect()->route('store-debts.index')->with('success', 'Deudor agregado.');
    }

    public function destroyDebtor(StoreDebtor $debtor): RedirectResponse
    {
        $debtor->delete();

        return redirect()->route('store-debts.index')->with('success', 'Cuenta de tienda eliminada.');
    }

    public function storeCharge(Request $request, StoreDebtor $debtor): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $debtor->movements()->create([
            ...$validated,
            'movement_date' => now()->toDateString(),
            'type' => 'charge',
        ]);

        return redirect()->route('store-debts.index')->with('success', 'Cargo registrado.');
    }

    public function storePayment(Request $request, StoreDebtor $debtor): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($debtor, $validated): void {
            $lockedDebtor = StoreDebtor::whereKey($debtor->id)->lockForUpdate()->firstOrFail();
            $amount = (float) $validated['amount'];

            if ($amount > $lockedDebtor->balance) {
                throw ValidationException::withMessages([
                    'amount' => 'El abono no puede ser mayor al saldo pendiente.',
                ]);
            }

            $lockedDebtor->movements()->create([
                ...$validated,
                'description' => $validated['description'] ?? 'Abono',
                'movement_date' => now()->toDateString(),
                'type' => 'payment',
            ]);
        });

        return redirect()->route('store-debts.index')->with('success', 'Abono de tienda registrado.');
    }
}
