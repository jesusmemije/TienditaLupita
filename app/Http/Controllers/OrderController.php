<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['client', 'items'])
            ->where('status', 'pending_delivery')
            ->latest()
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function create(): View
    {
        return view('orders.create', ['clients' => Client::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
        ]);

        DB::transaction(function () use ($validated): void {
            $total = collect($validated['items'])->sum(fn ($item) => (float) $item['price']);
            $order = Order::create([
                'client_id' => $validated['client_id'],
                'status' => 'pending_delivery',
                'total_amount' => $total,
                'paid_amount' => 0,
                'due_amount' => 0,
            ]);
            $order->items()->createMany($validated['items']);
        });

        return redirect()->route('orders.index')->with('success', 'Pedido creado.');
    }

    public function deliver(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'payment_type' => ['required', 'in:full,partial'],
            'paid_amount' => ['required_if:payment_type,partial', 'nullable', 'numeric', 'decimal:0,2', 'gt:0', 'lte:'.$order->total_amount],
        ]);

        DB::transaction(function () use ($order, $validated): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== 'pending_delivery') {
                throw ValidationException::withMessages(['order' => 'Este pedido ya fue entregado.']);
            }

            $paid = $validated['payment_type'] === 'full'
                ? (float) $lockedOrder->total_amount
                : (float) $validated['paid_amount'];
            $due = max(0, round((float) $lockedOrder->total_amount - $paid, 2));

            $lockedOrder->update([
                'paid_amount' => $paid,
                'due_amount' => $due,
                'status' => $due > 0 ? 'delivered_partial' : 'delivered_paid',
                'delivered_at' => now(),
            ]);
        });

        $redirect = redirect()->route('orders.index')->with('success', 'Entrega registrada.');

        if ($validated['payment_type'] === 'partial') {
            $order->refresh()->load(['client', 'items']);
            $redirect->with('whatsapp_url', $this->paymentWhatsAppUrl($order, (float) $validated['paid_amount']));
        }

        return $redirect;
    }

    public function debts(): View
    {
        $orders = Order::with(['client', 'items'])
            ->where('due_amount', '>', 0)
            ->latest('delivered_at')
            ->get();

        return view('debts.index', compact('orders'));
    }

    public function settle(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ((float) $lockedOrder->due_amount <= 0) {
                throw ValidationException::withMessages(['order' => 'Este pedido no tiene saldo pendiente.']);
            }

            $lockedOrder->update([
                'paid_amount' => $lockedOrder->total_amount,
                'due_amount' => 0,
                'status' => 'delivered_paid',
            ]);
        });

        return redirect()->route('debts.index')->with('success', 'Saldo liquidado.');
    }

    public function recordPayment(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'paid_amount' => ['required', 'numeric', 'decimal:0,2', 'gt:0'],
        ], [
            'paid_amount.required' => 'Por favor ingresa el monto a abonar',
            'paid_amount.numeric' => 'Por favor ingresa el monto a abonar',
            'paid_amount.gt' => 'Por favor ingresa el monto a abonar',
        ]);

        $paymentAmount = round((float) $validated['paid_amount'], 2);

        DB::transaction(function () use ($order, $paymentAmount): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($paymentAmount > (float) $lockedOrder->due_amount) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'El abono no puede ser mayor al saldo pendiente.',
                ]);
            }

            if ((float) $lockedOrder->due_amount <= 0) {
                throw ValidationException::withMessages(['order' => 'Este pedido no tiene saldo pendiente.']);
            }

            $due = round((float) $lockedOrder->due_amount - $paymentAmount, 2);
            $lockedOrder->update([
                'paid_amount' => round((float) $lockedOrder->paid_amount + $paymentAmount, 2),
                'due_amount' => $due,
                'status' => $due > 0 ? 'delivered_partial' : 'delivered_paid',
            ]);
        });

        $order->refresh()->load(['client', 'items']);

        return redirect()->route('debts.index')
            ->with('success', 'Abono registrado.')
            ->with('whatsapp_url', $this->paymentWhatsAppUrl($order, $paymentAmount));
    }

    private function paymentWhatsAppUrl(Order $order, float $paymentAmount): string
    {
        $details = $order->items
            ->map(fn ($item) => $item->product_name.' - $'.number_format((float) $item->price, 2))
            ->implode("\n");
        $message = "¡Hola {$order->client->name}! 😊 Muchas gracias por tu abono. Te comparto el resumen actualizado de tu pedido:\n\n"
            ."📦 *Detalle de tus productos:*\n{$details}\n\n"
            .'💰 *Total del pedido:* $'.number_format((float) $order->total_amount, 2)."\n"
            .'✅ *Abono/Anticipo recibido:* $'.number_format($paymentAmount, 2)."\n"
            .'📌 *Saldo pendiente a liquidar:* $'.number_format((float) $order->due_amount, 2)."\n\n"
            .'¡Muchas gracias por tu confianza y preferencia! Me avisas si necesitas algo más. ✨' ."\n\n"
            .'— Tiendita Lupita 🛍️';
        $phone = preg_replace('/\D+/', '', $order->client->phone);

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    public function history(): View
    {
        $orders = Order::with(['client', 'items'])
            ->whereIn('status', ['delivered_paid', 'delivered_partial'])
            ->latest('delivered_at')
            ->get();

        return view('orders.history', compact('orders'));
    }
}
