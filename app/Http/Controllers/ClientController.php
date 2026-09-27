<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $clients = Client::query()
            ->withSum(['orders as total_paid' => fn ($query) => $query->whereIn('status', ['delivered_paid', 'delivered_partial'])], 'paid_amount')
            ->withSum('orders as current_due', 'due_amount')
            ->withSum(['orders as pending_orders_total' => fn ($query) => $query->where('status', 'pending_delivery')], 'total_amount')
            ->orderBy('name')
            ->get();

        return view('clients.index', compact('clients', 'search'));
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'internal_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $client = Client::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $client->id,
                'name' => $client->name,
                'internal_name' => $client->internal_name,
            ]);
        }

        return redirect()->route('clients.index')->with('success', 'Cliente agregado.');
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'internal_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')->with('success', 'Cliente actualizado.');
    }
}
