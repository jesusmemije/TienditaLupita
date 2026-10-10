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

const themeToggle = document.getElementById('theme-toggle');

if (themeToggle) {
	const themeLabel = themeToggle.querySelector('[data-theme-label]');
	const darkIcon = themeToggle.querySelector('[data-theme-icon="dark"]');
	const lightIcon = themeToggle.querySelector('[data-theme-icon="light"]');

	const updateThemeControl = () => {
		const isDark = document.documentElement.dataset.theme === 'dark';
		const nextTheme = isDark ? 'light' : 'dark';
		themeLabel.textContent = isDark ? 'Claro' : 'Oscuro';
		themeToggle.setAttribute('aria-label', `Cambiar a modo ${nextTheme === 'dark' ? 'oscuro' : 'claro'}`);
		themeToggle.title = `Cambiar a modo ${nextTheme === 'dark' ? 'oscuro' : 'claro'}`;
		darkIcon.classList.toggle('hidden', isDark);
		lightIcon.classList.toggle('hidden', !isDark);
	};

	updateThemeControl();
	themeToggle.addEventListener('click', () => {
		const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
		document.documentElement.dataset.theme = nextTheme;

		try {
			localStorage.setItem('tiendita-theme', nextTheme);
		} catch {
			// The selected theme remains active for the current page.
		}

		updateThemeControl();
		window.dispatchEvent(new CustomEvent('tiendita:theme-change'));
	});
}

const dashboardCanvasElements = document.querySelectorAll('canvas[data-dashboard-chart]');

if (dashboardCanvasElements.length > 0) {
	import('chart.js').then(({ ArcElement, CategoryScale, Chart, DoughnutController, Filler, Legend, LineController, LineElement, LinearScale, PointElement, Tooltip }) => {
		Chart.register(ArcElement, CategoryScale, DoughnutController, Filler, Legend, LineController, LineElement, LinearScale, PointElement, Tooltip);

const formatChartMoney = new Intl.NumberFormat('es-MX', {
	style: 'currency',
	currency: 'MXN',
	maximumFractionDigits: 2,
});
const formatChartAxisMoney = new Intl.NumberFormat('es-MX', {
	style: 'currency',
	currency: 'MXN',
	notation: 'compact',
	maximumFractionDigits: 1,
});
const chartPalette = () => {
	const isDark = document.documentElement.dataset.theme === 'dark';

	return {
		brand: getComputedStyle(document.documentElement).getPropertyValue('--brand').trim(),
		collection: isDark ? '#6ee7b7' : '#0f766e',
		text: isDark ? '#f4f1ed' : '#292524',
		muted: isDark ? '#b9c0c2' : '#78716c',
		grid: isDark ? 'rgba(255, 255, 255, 0.09)' : 'rgba(41, 37, 36, 0.09)',
		surface: isDark ? '#222528' : '#ffffff',
		fill: isDark ? 'rgba(237, 120, 148, 0.14)' : 'rgba(198, 63, 100, 0.12)',
	};
};
const dashboardCharts = Array.from(dashboardCanvasElements).map((canvas) => {
	const kind = canvas.dataset.dashboardChart;
	const data = JSON.parse(canvas.dataset.chartData);
	const palette = chartPalette();
	const options = {
		responsive: true,
		maintainAspectRatio: false,
		animation: { duration: 450 },
		plugins: {
			legend: { position: 'bottom', labels: { color: palette.text, usePointStyle: true, boxWidth: 8, padding: 18 } },
			tooltip: {
				callbacks: {
					label: (context) => kind === 'sales'
						? `${context.dataset.label}: ${formatChartMoney.format(context.parsed.y || 0)}`
						: `${context.label}: ${formatChartMoney.format(context.parsed || 0)}`,
				},
			},
		},
	};

	if (kind === 'sales') {
		options.interaction = { mode: 'index', intersect: false };
		options.scales = {
			x: { grid: { display: false }, ticks: { color: palette.muted, maxRotation: 0 } },
			y: {
				beginAtZero: true,
				grid: { color: palette.grid },
				ticks: { color: palette.muted, callback: (value) => formatChartAxisMoney.format(value) },
			},
		};

		return {
			kind,
			chart: new Chart(canvas, {
				type: 'line',
				data: {
					labels: data.labels,
					datasets: [
						{ label: 'Ventas entregadas', data: data.sales, borderColor: palette.brand, backgroundColor: palette.fill, fill: true, tension: 0.36, borderWidth: 3, pointRadius: 3, pointHoverRadius: 5, pointBackgroundColor: palette.surface },
						{ label: 'Cobrado', data: data.collected, borderColor: palette.collection, backgroundColor: 'transparent', tension: 0.36, borderWidth: 2, borderDash: [5, 4], pointRadius: 2, pointHoverRadius: 4 },
					],
				},
				options,
			}),
		};
	}

	return {
		kind,
		chart: new Chart(canvas, {
			type: 'doughnut',
			data: {
				labels: ['Cobrado', 'Por cobrar'],
				datasets: [{ data: [data.collected, data.due], backgroundColor: [palette.collection, palette.brand], borderColor: palette.surface, borderWidth: 4, hoverOffset: 6 }],
			},
			options: { ...options, cutout: '72%' },
		}),
	};
});

window.addEventListener('tiendita:theme-change', () => {
	const palette = chartPalette();

	dashboardCharts.forEach(({ kind, chart }) => {
		chart.options.plugins.legend.labels.color = palette.text;

		if (kind === 'sales') {
			chart.options.scales.x.ticks.color = palette.muted;
			chart.options.scales.y.ticks.color = palette.muted;
			chart.options.scales.y.grid.color = palette.grid;
			chart.data.datasets[0].borderColor = palette.brand;
			chart.data.datasets[0].backgroundColor = palette.fill;
			chart.data.datasets[0].pointBackgroundColor = palette.surface;
			chart.data.datasets[1].borderColor = palette.collection;
		} else {
			chart.data.datasets[0].backgroundColor = [palette.collection, palette.brand];
			chart.data.datasets[0].borderColor = palette.surface;
		}

		chart.update();
	});
});
	}).catch((error) => console.error('No se pudieron cargar las gráficas del panel.', error));
}

Alpine.start();
