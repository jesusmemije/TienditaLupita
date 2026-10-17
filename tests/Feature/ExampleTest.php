<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\StoreDebtor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

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
        $client = Client::create([
            'name' => 'Ana Lopez',
            'internal_name' => 'Ana trabajo',
            'phone' => '5215512345678',
        ]);

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
        ])->assertRedirect(route('orders.index', ['whatsapp' => 1]));

        $order->refresh();
        $this->assertSame('delivered_partial', $order->status);
        $this->assertSame('600.00', $order->paid_amount);
        $this->assertSame('400.00', $order->due_amount);
        $this->get(route('debts.index'))
            ->assertOk()
            ->assertSee('$400.00')
            ->assertSee('form="settle-order-' . $order->id . '"', false)
            ->assertSee('id="settle-order-' . $order->id . '"', false);

        $this->from(route('debts.index'))->post(route('debts.payments.store', $order), [
            'paid_amount' => '100.00',
        ])->assertRedirect(route('debts.index', ['whatsapp' => 1]))
            ->assertSessionHas('whatsapp_url', function (string $url): bool {
                $message = rawurldecode((string) parse_url($url, PHP_URL_QUERY));

                return str_contains($url, 'wa.me/5215512345678')
                    && str_contains($message, 'Ana Lopez')
                    && ! str_contains($message, 'Ana trabajo')
                    && str_contains($message, 'Saldo restante a liquidar: $300.00');
            });

        $this->assertSame('700.00', $order->fresh()->paid_amount);
        $this->assertSame('300.00', $order->fresh()->due_amount);

        $this->post(route('debts.settle', $order))->assertRedirect(route('debts.index'));
        $this->assertSame('1000.00', $order->fresh()->paid_amount);
        $this->assertSame('0.00', $order->fresh()->due_amount);
        $this->assertSame('delivered_paid', $order->fresh()->status);
    }

    public function test_full_delivery_ignores_the_partial_payment_amount(): void
    {
        $client = Client::create([
            'name' => 'Ana Lopez',
            'phone' => '5215512345678',
        ]);

        $this->post(route('orders.store'), [
            'client_id' => $client->id,
            'items' => [['product_name' => 'Blusa', 'price' => '600.00']],
        ])->assertRedirect(route('orders.index'));

        $order = Order::firstOrFail();

        $this->post(route('orders.deliver', $order), [
            'payment_type' => 'full',
            'paid_amount' => '0',
        ])->assertRedirect(route('orders.index'));

        $this->assertSame('600.00', $order->fresh()->paid_amount);
        $this->assertSame('0.00', $order->fresh()->due_amount);
        $this->assertSame('delivered_paid', $order->fresh()->status);
    }

    public function test_partial_delivery_can_be_recorded_with_zero_payment(): void
    {
        $client = Client::create(['name' => 'Ana Lopez', 'phone' => '5215512345678']);
        $order = Order::create([
            'client_id' => $client->id,
            'status' => 'pending_delivery',
            'total_amount' => 600,
            'paid_amount' => 0,
            'due_amount' => 0,
        ]);

        $this->post(route('orders.deliver', $order), [
            'payment_type' => 'partial',
            'paid_amount' => '0',
        ])->assertRedirect(route('orders.index', ['whatsapp' => 1]));

        $this->assertSame('delivered_partial', $order->fresh()->status);
        $this->assertSame('0.00', $order->fresh()->paid_amount);
        $this->assertSame('600.00', $order->fresh()->due_amount);
    }

    public function test_pending_order_can_be_deleted_with_its_items(): void
    {
        $client = Client::create(['name' => 'Ana Lopez', 'phone' => '5215512345678']);
        $order = Order::create([
            'client_id' => $client->id,
            'status' => 'pending_delivery',
            'total_amount' => 600,
            'paid_amount' => 0,
            'due_amount' => 0,
        ]);
        $order->items()->create(['product_name' => 'Blusa', 'price' => 600]);

        $this->delete(route('orders.destroy', $order))
            ->assertRedirect(route('orders.index'))
            ->assertSessionHas('success', 'Pedido eliminado. Puedes registrarlo nuevamente.');

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['order_id' => $order->id]);
    }

    public function test_delivered_order_cannot_be_deleted(): void
    {
        $client = Client::create(['name' => 'Ana Lopez', 'phone' => '5215512345678']);
        $order = Order::create([
            'client_id' => $client->id,
            'status' => 'delivered_paid',
            'total_amount' => 600,
            'paid_amount' => 600,
            'due_amount' => 0,
            'delivered_at' => now(),
        ]);

        $this->from(route('orders.index'))
            ->delete(route('orders.destroy', $order))
            ->assertRedirect(route('orders.index'))
            ->assertSessionHasErrors('order');

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_admin_dashboard_summarizes_sales_and_ranks_top_clients_by_delivered_orders(): void
    {
        $client = Client::create(['name' => 'Ana Lopez', 'phone' => '5215512345678']);
        $secondClient = Client::create(['name' => 'Bea Torres', 'phone' => '5215512345679']);

        Order::create([
            'client_id' => $client->id,
            'status' => 'delivered_paid',
            'total_amount' => 100,
            'paid_amount' => 100,
            'due_amount' => 0,
            'delivered_at' => now(),
        ]);
        Order::create([
            'client_id' => $client->id,
            'status' => 'delivered_partial',
            'total_amount' => 200,
            'paid_amount' => 50,
            'due_amount' => 150,
            'delivered_at' => now()->subDays(2),
        ]);
        Order::create([
            'client_id' => $secondClient->id,
            'status' => 'pending_delivery',
            'total_amount' => 250,
            'paid_amount' => 0,
            'due_amount' => 0,
        ]);
        Order::create([
            'client_id' => $client->id,
            'status' => 'pending_delivery',
            'total_amount' => 300,
            'paid_amount' => 0,
            'due_amount' => 0,
        ]);

        $this->get(route('orders.index'))
            ->assertOk()
            ->assertViewHas('salesThisMonth', 300.0)
            ->assertViewHas('collectedThisMonth', 150.0)
            ->assertViewHas('currentDue', 150.0)
            ->assertViewHas('pendingOrderCount', 1)
            ->assertViewHas('topClients', fn($clients): bool => $clients->count() === 1
                && $clients->first()->id === $client->id
                && (float) $clients->first()->delivered_sales === 150.0
                && $clients->first()->delivered_order_count === 2);
    }

    public function test_main_mobile_pages_render(): void
    {
        $this->get(route('orders.create'))->assertOk()->assertSee('Crear pedido');
        $this->get(route('debts.index'))->assertOk()->assertSee('Cuentas pendientes');
        $this->get(route('store-debts.index'))->assertOk()->assertSee('Cuentas independientes de los pedidos SHEIN');
        $this->get(route('clients.index'))->assertOk()->assertSee('Clientes');
        $this->get(route('orders.history'))->assertOk()->assertSee('Pedidos entregados');
    }

    public function test_store_fiado_tracks_charges_and_payments_separately(): void
    {
        $this->post(route('store-debts.store'), ['name' => 'Marta tienda'])
            ->assertRedirect(route('store-debts.index'));
        $debtor = StoreDebtor::firstOrFail();

        $this->post(route('store-debts.charges.store', $debtor), [
            'description' => 'Sueter azul',
            'amount' => '250.00',
            'movement_date' => '2026-09-26',
        ])->assertRedirect(route('store-debts.index'));

        $this->post(route('store-debts.payments.store', $debtor), [
            'description' => 'Abono efectivo',
            'amount' => '50.00',
            'movement_date' => '2026-09-26',
        ])->assertRedirect(route('store-debts.index'));

        $this->assertSame(200.0, $debtor->fresh()->balance);
        $this->assertDatabaseCount('store_debt_movements', 2);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_client_internal_identifier_can_be_updated_without_changing_formal_name(): void
    {
        $client = Client::create(['name' => 'Nati', 'phone' => '5215512345678']);

        $this->put(route('clients.update', $client), [
            'name' => 'Nati',
            'internal_name' => 'Nati trabajo',
            'phone' => '5215512345678',
        ])->assertRedirect(route('clients.index'));

        $this->assertSame('Nati', $client->fresh()->name);
        $this->assertSame('Nati trabajo', $client->fresh()->internal_name);
    }

    public function test_shein_payment_requires_an_amount(): void
    {
        $client = Client::create(['name' => 'Ana', 'phone' => '5215512345678']);
        $order = Order::create([
            'client_id' => $client->id,
            'status' => 'delivered_partial',
            'total_amount' => 100,
            'paid_amount' => 25,
            'due_amount' => 75,
            'delivered_at' => now(),
        ]);

        $this->from(route('debts.index'))
            ->post(route('debts.payments.store', $order), ['paid_amount' => '0'])
            ->assertSessionHasErrors('paid_amount');

        $this->assertSame('75.00', $order->fresh()->due_amount);
    }

    public function test_admin_pages_require_authentication_and_public_registration_is_unavailable(): void
    {
        Auth::logout();

        $this->get(route('orders.create'))->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/register')->assertNotFound();
        $this->get(route('login'))->assertOk()->assertSee('Iniciar sesión');
        $this->get(route('home'))->assertOk()->assertSee('Tiendita Lupita');
    }
}
