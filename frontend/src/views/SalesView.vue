<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1 class="page-title">Punto de Venta</h1>
        <p class="page-sub">Cajero: {{ auth.user?.name }}</p>
      </div>
    </div>

    <div class="pos-layout">
      <!-- Panel izquierdo: catálogo -->
      <div class="catalog-panel">
        <div class="search-wrap">
          <i class="ti ti-search search-icon" aria-hidden="true"></i>
          <input v-model="search" type="text" class="search-input" placeholder="Buscar producto…" />
        </div>

        <div class="product-list">
          <div
            v-for="p in filteredProducts"
            :key="p.id"
            class="product-row"
            @click="addToCart(p)"
          >
            <div class="product-info">
              <span class="product-name">{{ p.name }}</span>
              <span class="product-meta">{{ p.sku }} · {{ p.stock }} en stock</span>
            </div>
            <span class="product-price">${{ Number(p.price).toFixed(2) }}</span>
          </div>
          <div v-if="filteredProducts.length === 0" class="list-empty">
            Sin resultados
          </div>
        </div>
      </div>

      <!-- Panel derecho: carrito + cobro -->
      <div class="checkout-panel">
        <div class="cart-section">
          <div class="cart-header">
            <span class="cart-label">Carrito</span>
            <span v-if="cart.length" class="cart-count">{{ cart.length }} ítem{{ cart.length > 1 ? 's' : '' }}</span>
          </div>

          <div v-if="cart.length === 0" class="cart-empty">
            <i class="ti ti-shopping-cart" aria-hidden="true"></i>
            <span>Selecciona productos</span>
          </div>

          <div v-else class="cart-items">
            <div v-for="item in cart" :key="item.id" class="cart-item">
              <div class="item-name">{{ item.name }}</div>
              <div class="item-controls">
                <button class="qty-btn" @click="decrementItem(item)">−</button>
                <span class="qty-val">{{ item.quantity }}</span>
                <button class="qty-btn" @click="incrementItem(item)">+</button>
              </div>
              <span class="item-subtotal">${{ (Number(item.price) * item.quantity).toFixed(2) }}</span>
              <button class="remove-btn" @click="removeItem(item)">
                <i class="ti ti-x" aria-hidden="true"></i>
              </button>
            </div>
          </div>

          <div class="cart-totals">
            <div class="total-row">
              <span class="total-label">Total</span>
              <span class="total-value">${{ subtotal.toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <div class="payment-section">
          <div class="pay-row">
            <div class="pay-field">
              <label class="field-label">Método</label>
              <select v-model="paymentMethod" class="field-input">
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta">Tarjeta</option>
                <option value="transferencia">Transferencia</option>
              </select>
            </div>
            <div class="pay-field">
              <label class="field-label">Recibido</label>
              <input v-model="paid" type="number" min="0" class="field-input" placeholder="0.00" />
            </div>
          </div>

          <div v-if="Number(paid) >= subtotal && cart.length > 0" class="change-bar">
            <span class="change-label">Cambio</span>
            <span class="change-val">${{ (Number(paid) - subtotal).toFixed(2) }}</span>
          </div>

          <button
            class="charge-btn"
            :disabled="cart.length === 0 || Number(paid) < subtotal || loading"
            @click="processSale"
          >
            <i class="ti ti-check" aria-hidden="true"></i>
            {{ loading ? 'Procesando…' : 'Cobrar $' + subtotal.toFixed(2) }}
          </button>
        </div>
      </div>
    </div>

    <!-- Historial -->
    <div class="history-section">
      <div class="section-header">
        <span class="section-title">Ventas del día</span>
        <span v-if="todaySales.length" class="section-badge-green">{{ todaySales.length }} ventas</span>
      </div>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Folio</th>
              <th>Hora</th>
              <th>Método</th>
              <th class="th-right">Total</th>
              <th class="th-center">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="s in todaySales" :key="s.id" class="table-row">
              <td class="td-mono td-accent">{{ s.folio }}</td>
              <td class="td-muted">{{ formatTime(s.created_at) }}</td>
              <td class="td-muted td-cap">{{ s.payment_method }}</td>
              <td class="td-right td-price">${{ Number(s.total).toFixed(2) }}</td>
              <td class="td-center">
                <span :class="s.status === 'completada' ? 'badge-success' : 'badge-danger'">{{ s.status }}</span>
              </td>
            </tr>
            <tr v-if="todaySales.length === 0">
              <td colspan="5" class="td-empty">Sin ventas hoy</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal ticket -->
    <div v-if="lastSale" class="modal-overlay" @click.self="lastSale = null">
      <div class="ticket-modal">
        <div class="ticket-check"><i class="ti ti-check" aria-hidden="true"></i></div>
        <h2 class="ticket-title">Venta completada</h2>
        <span class="ticket-folio">{{ lastSale.folio }}</span>
        <div class="ticket-rows">
          <div class="ticket-row">
            <span>Total</span>
            <span class="ticket-val">${{ Number(lastSale.total).toFixed(2) }}</span>
          </div>
          <div class="ticket-row">
            <span>Pagado</span>
            <span>${{ Number(lastSale.paid).toFixed(2) }}</span>
          </div>
          <div class="ticket-row">
            <span>Cambio</span>
            <span class="ticket-change">${{ Number(lastSale.change).toFixed(2) }}</span>
          </div>
        </div>
        <button class="btn-primary w-full" @click="lastSale = null">Nueva venta</button>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import { useAuthStore } from '../stores/auth'
import api from '../api/axios'

const auth          = useAuthStore()
const products      = ref([])
const cart          = ref([])
const search        = ref('')
const paymentMethod = ref('efectivo')
const paid          = ref(0)
const loading       = ref(false)
const lastSale      = ref(null)
const todaySales    = ref([])

const filteredProducts = computed(() =>
  products.value.filter(p =>
    p.name.toLowerCase().includes(search.value.toLowerCase()) ||
    p.sku.toLowerCase().includes(search.value.toLowerCase())
  )
)

const subtotal = computed(() =>
  cart.value.reduce((sum, i) => sum + Number(i.price) * i.quantity, 0)
)

function addToCart(product) {
  const existing = cart.value.find(i => i.id === product.id)
  if (existing) { if (existing.quantity < product.stock) existing.quantity++ }
  else cart.value.push({ ...product, quantity: 1 })
}

function incrementItem(item) {
  const p = products.value.find(p => p.id === item.id)
  if (item.quantity < p.stock) item.quantity++
}

function decrementItem(item) {
  if (item.quantity > 1) item.quantity--
  else removeItem(item)
}

function removeItem(item) { cart.value = cart.value.filter(i => i.id !== item.id) }

async function processSale() {
  loading.value = true
  try {
    const { data } = await api.post('/sales', {
      items: cart.value.map(i => ({ product_id: i.id, quantity: i.quantity })),
      payment_method: paymentMethod.value,
      paid: paid.value,
    })
    lastSale.value = data
    cart.value     = []
    paid.value     = 0
    await loadData()
  } catch (e) { alert(e.response?.data?.message || 'Error al procesar la venta') }
  finally { loading.value = false }
}

function formatTime(d) {
  return new Date(d).toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' })
}

async function loadData() {
  const [prodRes, salesRes] = await Promise.all([api.get('/products'), api.get('/sales')])
  products.value   = prodRes.data
  todaySales.value = salesRes.data.filter(s =>
    new Date(s.created_at).toDateString() === new Date().toDateString()
  )
}

onMounted(loadData)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap');
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/tabler-icons.min.css');

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; }

.page-header { margin-bottom: 20px; }
.page-title { font-size: 22px; font-weight: 600; color: #F4F4F2; letter-spacing: -0.02em; }
.page-sub { font-size: 13px; color: #5C5C5A; margin-top: 2px; }

.pos-layout {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 16px;
  margin-bottom: 28px;
  height: 520px;
}

.catalog-panel {
  background: #1E1E20;
  border: 1px solid #2A2A2C;
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.search-wrap {
  position: relative;
  padding: 12px;
  border-bottom: 1px solid #2A2A2C;
}

.search-icon { position: absolute; left: 22px; top: 50%; transform: translateY(-50%); font-size: 15px; color: #5C5C5A; }
.search-input { width: 100%; background: #242426; border: 1px solid #2E2E30; color: #E4E4E2; font-family: 'DM Sans', sans-serif; font-size: 13px; padding: 8px 12px 8px 34px; border-radius: 8px; outline: none; transition: border-color 0.15s; }
.search-input::placeholder { color: #4A4A48; }
.search-input:focus { border-color: #D97706; }

.product-list { flex: 1; overflow-y: auto; }

.product-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 11px 16px;
  border-bottom: 1px solid #242426;
  cursor: pointer;
  transition: background 0.1s;
}

.product-row:last-child { border-bottom: none; }
.product-row:hover { background: #242426; }

.product-info { display: flex; flex-direction: column; gap: 2px; }
.product-name { font-size: 13px; font-weight: 500; color: #D4D4D2; }
.product-meta { font-size: 11px; color: #5C5C5A; font-family: monospace; }
.product-price { font-size: 14px; font-weight: 600; color: #34D399; flex-shrink: 0; }

.list-empty { padding: 32px; text-align: center; color: #4A4A48; font-size: 13px; }

.checkout-panel {
  background: #1E1E20;
  border: 1px solid #2A2A2C;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.cart-section { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

.cart-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #2A2A2C;
}

.cart-label { font-size: 12px; font-weight: 500; color: #6C6C6A; text-transform: uppercase; letter-spacing: 0.06em; }
.cart-count { font-size: 11px; background: #242426; color: #9C9C9A; padding: 2px 7px; border-radius: 99px; }

.cart-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: #4A4A48;
  font-size: 13px;
}

.cart-empty .ti { font-size: 28px; }

.cart-items { flex: 1; overflow-y: auto; padding: 8px; display: flex; flex-direction: column; gap: 4px; }

.cart-item {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #242426;
  border-radius: 7px;
  padding: 8px 10px;
}

.item-name { flex: 1; font-size: 12px; color: #C4C4C2; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.item-controls { display: flex; align-items: center; gap: 4px; flex-shrink: 0; }

.qty-btn {
  width: 22px;
  height: 22px;
  background: #2E2E30;
  border: none;
  color: #9C9C9A;
  font-size: 14px;
  border-radius: 5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.1s;
}

.qty-btn:hover { background: #3A3A3C; color: #E4E4E2; }
.qty-val { font-size: 13px; font-weight: 500; color: #E4E4E2; width: 20px; text-align: center; }
.item-subtotal { font-size: 12px; font-weight: 500; color: #D4D4D2; flex-shrink: 0; width: 52px; text-align: right; }

.remove-btn { background: none; border: none; color: #4A4A48; font-size: 13px; cursor: pointer; padding: 2px; border-radius: 4px; transition: color 0.1s; }
.remove-btn:hover { color: #F87171; }

.cart-totals {
  padding: 12px 16px;
  border-top: 1px solid #2A2A2C;
}

.total-row { display: flex; align-items: center; justify-content: space-between; }
.total-label { font-size: 12px; font-weight: 500; color: #6C6C6A; text-transform: uppercase; letter-spacing: 0.06em; }
.total-value { font-size: 22px; font-weight: 600; color: #F4F4F2; letter-spacing: -0.02em; }

.payment-section {
  padding: 14px 16px;
  border-top: 1px solid #2A2A2C;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.pay-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.pay-field { display: flex; flex-direction: column; gap: 5px; }
.field-label { font-size: 10px; font-weight: 500; color: #6C6C6A; text-transform: uppercase; letter-spacing: 0.06em; }
.field-input { background: #242426; border: 1px solid #2E2E30; color: #E4E4E2; font-family: 'DM Sans', sans-serif; font-size: 13px; padding: 8px 10px; border-radius: 7px; outline: none; transition: border-color 0.15s; }
.field-input:focus { border-color: #D97706; }

.change-bar { display: flex; align-items: center; justify-content: space-between; background: #0C2218; border: 1px solid #163D28; border-radius: 8px; padding: 8px 12px; }
.change-label { font-size: 12px; color: #34D399; }
.change-val { font-size: 16px; font-weight: 600; color: #34D399; }

.charge-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  background: #16A34A;
  color: #fff;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  font-weight: 500;
  padding: 11px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s, transform 0.1s;
}

.charge-btn:hover:not(:disabled) { background: #15803D; }
.charge-btn:active:not(:disabled) { transform: scale(0.98); }
.charge-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.charge-btn .ti { font-size: 16px; }

.history-section { margin-top: 8px; }
.section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
.section-title { font-size: 12px; font-weight: 500; color: #6C6C6A; text-transform: uppercase; letter-spacing: 0.06em; }
.section-badge-green { font-size: 11px; background: #0C2218; color: #34D399; border: 1px solid #163D28; padding: 1px 7px; border-radius: 99px; }

.table-wrap { background: #1E1E20; border: 1px solid #2A2A2C; border-radius: 10px; overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table thead th { background: #222224; color: #6C6C6A; font-weight: 500; font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; padding: 10px 14px; text-align: left; border-bottom: 1px solid #2A2A2C; }
.th-right { text-align: right !important; }
.th-center { text-align: center !important; }
.table-row { border-bottom: 1px solid #242426; transition: background 0.1s; }
.table-row:last-child { border-bottom: none; }
.table-row:hover { background: #222224; }
.data-table td { padding: 10px 14px; color: #C4C4C2; vertical-align: middle; }
.td-mono { font-family: monospace; font-size: 11px; }
.td-accent { color: #60A5FA; }
.td-muted { color: #6C6C6A; }
.td-cap { text-transform: capitalize; }
.td-right { text-align: right; }
.td-center { text-align: center; }
.td-price { color: #34D399; font-weight: 500; }
.td-empty { text-align: center; padding: 32px !important; color: #4A4A48; font-size: 13px; }

.badge-success { display: inline-flex; font-size: 11px; padding: 2px 8px; border-radius: 99px; background: #0C2218; color: #34D399; border: 1px solid #163D28; }
.badge-danger { display: inline-flex; font-size: 11px; padding: 2px 8px; border-radius: 99px; background: #2A0A0A; color: #F87171; border: 1px solid #3D1414; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(2px); }

.ticket-modal {
  background: #1E1E20;
  border: 1px solid #2E2E30;
  border-radius: 12px;
  padding: 28px 24px;
  width: 300px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.ticket-check { width: 48px; height: 48px; background: #0C2218; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #34D399; }
.ticket-title { font-size: 16px; font-weight: 600; color: #F4F4F2; }
.ticket-folio { font-family: monospace; font-size: 13px; color: #60A5FA; background: #0A1E38; border: 1px solid #1E2A3D; padding: 4px 12px; border-radius: 6px; }

.ticket-rows { width: 100%; background: #242426; border-radius: 8px; padding: 12px; display: flex; flex-direction: column; gap: 8px; }
.ticket-row { display: flex; align-items: center; justify-content: space-between; font-size: 13px; color: #8C8C8A; }
.ticket-val { color: #E4E4E2; font-weight: 500; }
.ticket-change { color: #34D399; font-weight: 600; }

.btn-primary { display: flex; align-items: center; justify-content: center; gap: 6px; background: #D97706; color: #fff; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; padding: 10px 14px; border: none; border-radius: 8px; cursor: pointer; transition: background 0.15s; width: 100%; }
.btn-primary:hover { background: #B45309; }
</style>