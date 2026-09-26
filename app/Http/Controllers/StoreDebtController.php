<?php

namespace App\Http\Controllers;

use App\Models\StoreDebtor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StoreDebtController extends Controller
{
    public function index(): View
    {
        $debtors = StoreDebtor::with(['movements' => fn ($query) => $query
            ->orderByDesc('movement_date')->orderByDesc('id')])
            ->orderBy('name')
            ->get();

        return view('store-debts.index', compact('debtors'));
    }

    public function storeDebtor(Request $request): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:255']]);
        StoreDebtor::create($validated);

        return redirect()->route('store-debts.index')->with('success', 'Deudor agregado.');
    }

    public function storeCharge(Request $request, StoreDebtor $debtor): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'description' => ['required', 'string', 'max:255'],
            'movement_date' => ['required', 'date'],
        ]);

        $debtor->movements()->create([
            ...$validated,
            'type' => 'charge',
        ]);

        return redirect()->route('store-debts.index')->with('success', 'Cargo registrado.');
    }

    public function storePayment(Request $request, StoreDebtor $debtor): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
            'description' => ['nullable', 'string', 'max:255'],
            'movement_date' => ['required', 'date'],
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
                'type' => 'payment',
            ]);
        });

        return redirect()->route('store-debts.index')->with('success', 'Abono de tienda registrado.');
    }
}
