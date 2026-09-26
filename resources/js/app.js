import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.orderForm = (clients) => ({
	clients,
	items: [{ key: Date.now(), product_name: '', price: '' }],
	selectedClient: '',
	showClientModal: false,
	savingClient: false,
	clientError: '',
	newClient: { name: '', internal_name: '', phone: '' },
	get total() {
		return this.items.reduce((sum, item) => sum + (Number(item.price) || 0), 0);
	},
	formatMoney(value) {
		return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value || 0);
	},
	addItem() {
		this.items.push({ key: Date.now() + Math.random(), product_name: '', price: '' });
	},
	async createClient() {
		this.savingClient = true;
		this.clientError = '';

		try {
			const response = await fetch('/clients', {
				method: 'POST',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json',
					'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
				},
				body: JSON.stringify(this.newClient),
			});
			const data = await response.json();

			if (!response.ok) {
				this.clientError = Object.values(data.errors || {}).flat()[0] || 'No se pudo guardar el cliente.';
				return;
			}

			this.clients.push(data);
			this.selectedClient = String(data.id);
			this.newClient = { name: '', internal_name: '', phone: '' };
			this.showClientModal = false;
		} catch {
			this.clientError = 'No se pudo conectar. Intenta de nuevo.';
		} finally {
			this.savingClient = false;
		}
	},
});

Alpine.start();
