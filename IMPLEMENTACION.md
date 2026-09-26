# Implementación de Tiendita Lupita

## Puesta en marcha

1. Configura la conexión de base de datos de Laravel en `.env` (SQLite o MySQL de XAMPP).
2. Define las credenciales del administrador en `.env`; el seeder no contiene contraseñas predeterminadas:

```dotenv
ADMIN_NAME="Administradora"
ADMIN_EMAIL=admin@tienditalupita.com
ADMIN_PASSWORD="una-clave-segura"
TIENDITA_CONTACT_PHONE=5215512345678
TIENDITA_LOCATION="Dirección pública de la tienda"
```

3. Limpia la configuración cacheada y ejecuta las migraciones con el seeder:

```powershell
php artisan config:clear
php artisan migrate --seed
```

4. Inicia Laravel con `php artisan serve` o accede desde Apache/XAMPP al directorio `public`.
5. La página pública está en `/`; el ingreso administrativo está en `/login`. No existe una ruta de registro público.

## Persistencia

- `clients.name` conserva el nombre formal usado en mensajes al cliente; `clients.internal_name` es opcional y solo sirve para identificar a la persona dentro del panel.
- `orders` y `order_items` continúan gestionando el flujo SHEIN. Los abonos actualizan `paid_amount` y `due_amount` dentro de una transacción; liquidar fija el saldo en cero.
- `store_debtors` almacena los nombres libres del fiado físico, sin clave foránea a `clients`.
- `store_debt_movements` registra `amount`, `type` (`charge` o `payment`), `description`, `movement_date` y la relación con `store_debtors`. El saldo se calcula como cargos menos pagos.
- El usuario administrador vive en la tabla estándar `users`; `Database\Seeders\UserSeeder` lee los valores `ADMIN_*` de configuración.

## Rutas y controladores

- `GET /` sirve `resources/views/home.blade.php` sin requerir sesión.
- `GET|POST /login` usa `AuthController`; `POST /logout` invalida la sesión.
- `/admin`, `/dashboard`, y las rutas existentes de pedidos, clientes e historial requieren `auth`. Los clientes pueden actualizar su nombre formal e identificador interno en el panel.
- `/debts/{order}/payments` registra un abono SHEIN, valida monto positivo y saldo disponible, y entrega en sesión un enlace WhatsApp con el resumen actualizado.
- `/store-debts` y sus endpoints de cargos/abonos usan `StoreDebtController`, `StoreDebtor` y `StoreDebtMovement`; este módulo no consulta clientes ni pedidos SHEIN.

## Vistas

- `resources/views/home.blade.php`: landing pública, servicios, beneficios, contacto y acceso discreto a administración.
- `resources/views/auth/login.blade.php`: login con opción de mantener sesión.
- `resources/views/orders/*`, `debts/index.blade.php` y `clients/index.blade.php`: panel SHEIN y agenda con nombre formal separado del identificador interno.
- `resources/views/store-debts/index.blade.php`: tarjetas de deudores, historial, saldo y formularios rápidos de movimiento.
- `resources/views/layouts/app.blade.php`: navegación protegida y acción para enviar el resumen WhatsApp generado después de un abono.
