import './bootstrap';
import Alpine from 'alpinejs';
import $ from 'jquery';
import select2 from 'select2';

import 'select2/dist/css/select2.css';

window.$ = window.jQuery = $;
select2(window, $);

window.Alpine = Alpine;

window.liveSearch = () => ({
	query: '',
	root: null,
	init(root, initialQuery = '') {
		this.root = root;
		this.query = initialQuery;
	},
	normalize(value) {
		return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase();
	},
	matches(value) {
		const query = this.normalize(this.query.trim());

		return query === '' || this.normalize(value).includes(query);
	},
	visibleCount() {
	return Array.from(this.root.querySelectorAll('[data-live-search-item]'))
			.filter((item) => this.matches(item.dataset.search || '')).length;
	},
	hasVisibleItems() {
		return this.visibleCount() > 0;
	},
});

window.orderForm = (clients) => ({
	clients,
	items: [{ key: Date.now(), product_name: '', price: '' }],
	selectedClient: '',
	showClientModal: false,
	savingClient: false,
	clientError: '',
	newClient: { name: '', internal_name: '', phone: '' },
	initClientSelect(element) {
		const select = $(element);

		select.select2({
			width: '100%',
			placeholder: 'Elige un cliente',
			minimumResultsForSearch: 0,
			matcher: (params, option) => {
				const query = this.normalizeClientSearch(params.term || '');

				if (query === '') {
					return option;
				}

				const searchableName = option.element?.dataset.search || option.text;

				return this.normalizeClientSearch(searchableName).includes(query) ? option : null;
			},
		});

		select.on('change', () => {
			this.selectedClient = select.val() || '';
		});

		this.$watch('selectedClient', (value) => {
			if (String(select.val() || '') !== String(value || '')) {
				select.val(value || '').trigger('change');
			}
		});
	},
	normalizeClientSearch(value) {
		return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase();
	},
	clientLabel(client) {
		return client.internal_name ? `${client.name} · ${client.internal_name}` : client.name;
	},
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
			const clientId = String(data.id);
			await this.$nextTick();
			this.selectedClient = clientId;
			$(this.$refs.clientSelect).val(clientId).trigger('change');
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
