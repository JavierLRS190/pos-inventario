# 🏪 POS Inventario

Sistema de punto de venta con gestión de inventario y bodega para pequeños negocios (ferreterías, tiendas, papelerías).

## ✨ Funcionalidades

- 🔐 Autenticación con Laravel Sanctum + roles (admin, cajero, bodeguero)
- 📦 Gestión de productos, categorías e inventario
- 🏬 Control de bodega con entradas, salidas y ajustes de stock
- 🛒 Punto de venta con folio automático y cálculo de cambio
- 📊 Reportes: ventas por fecha, productos más vendidos, corte de caja
- ⚠️ Alertas de stock mínimo

## 🛠️ Stack

- **Backend:** Laravel 11, PHP 8.3
- **Base de datos:** MySQL 8.4
- **Autenticación:** Laravel Sanctum
- **Frontend:** Vue 3 (próximamente)

## ⚙️ Instalación

```bash
git clone https://github.com/JavierLRS190/pos-inventario.git
cd pos-inventario
composer install
cp .env.example .env
# Configura tu DB en .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## 👥 Usuarios de prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | admin@pos.com | admin1234 |
| Cajero | cajero@pos.com | cajero1234 |
| Bodeguero | bodeguero@pos.com | bodeguero1234 |

## 📖 Documentación API

Ver [docs/API.md](docs/API.md) para la documentación completa de endpoints.

## 📦 Módulos

- [x] Base de datos y modelos
- [x] Autenticación con roles
- [x] Gestión de productos e inventario
- [x] Control de bodega
- [x] Punto de venta
- [x] Reportes y corte de caja
- [ ] Frontend Vue 3
- [ ] Impresión de tickets
- [ ] Exportación a PDF/Excel