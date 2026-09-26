<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_an_order_can_be_partially_paid_and_settled_later(): void
    {
        $client = Client::create(['name' => 'Ana Lopez', 'phone' => '5215512345678']);

        $this->post(route('orders.store'), [
            'client_id' => $client->id,
            'items' => [
                ['product_name' => 'Blusa', 'price' => '600.00'],
                ['product_name' => 'Bolsa', 'price' => '400.00'],
            ],
        ])->assertRedirect(route('orders.index'));

        $order = Order::firstOrFail();
        $this->assertSame('1000.00', $order->total_amount);
        $this->assertSame('pending_delivery', $order->status);

        $this->post(route('orders.deliver', $order), [
            'payment_type' => 'partial',
            'paid_amount' => '600.00',
        ])->assertRedirect(route('orders.index'));

        $order->refresh();
        $this->assertSame('delivered_partial', $order->status);
        $this->assertSame('600.00', $order->paid_amount);
        $this->assertSame('400.00', $order->due_amount);
        $this->get(route('debts.index'))->assertOk()->assertSee('$400.00');

        $this->post(route('debts.settle', $order))->assertRedirect(route('debts.index'));
        $this->assertSame('1000.00', $order->fresh()->paid_amount);
        $this->assertSame('0.00', $order->fresh()->due_amount);
        $this->assertSame('delivered_paid', $order->fresh()->status);
    }

    public function test_main_mobile_pages_render(): void
    {
        $this->get(route('orders.create'))->assertOk()->assertSee('Crear pedido');
        $this->get(route('debts.index'))->assertOk()->assertSee('Cuentas pendientes');
        $this->get(route('clients.index'))->assertOk()->assertSee('Clientes');
        $this->get(route('orders.history'))->assertOk()->assertSee('Pedidos entregados');
    }
}
