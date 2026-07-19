<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1 class="page-title">Bodega</h1>
        <p class="page-sub">Control de entradas y salidas de inventario</p>
      </div>
      <button class="btn-primary" @click="showModal = true">
        <i class="ti ti-plus" aria-hidden="true"></i>
        Registrar movimiento
      </button>
    </div>

    <div class="stats-row">
      <div class="stat-pill stat-pill--green">
        <i class="ti ti-arrow-bar-down" aria-hidden="true"></i>
        <span class="stat-pill-val">{{ totalEntradas }}</span>
        <span class="stat-pill-label">Entradas hoy</span>
      </div>
      <div class="stat-pill stat-pill--red">
        <i class="ti ti-arrow-bar-up" aria-hidden="true"></i>
        <span class="stat-pill-val">{{ totalSalidas }}</span>
        <span class="stat-pill-label">Salidas hoy</span>
      </div>
      <div class="stat-pill stat-pill--amber">
        <i class="ti ti-adjustments" aria-hidden="true"></i>
        <span class="stat-pill-val">{{ totalAjustes }}</span>
        <span class="stat-pill-label">Ajustes hoy</span>
      </div>
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Producto</th>
            <th>Tipo</th>
            <th>Bodega</th>
            <th class="th-right">Cantidad</th>
            <th class="th-right">Antes</th>
            <th class="th-right">Después</th>
            <th>Motivo</th>
            <th>Fecha</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in movements" :key="m.id" class="table-row">
            <td class="td-name">{{ m.product?.name }}</td>
            <td>
              <span :class="{
                'badge-success': m.type === 'entrada',
                'badge-danger':  m.type === 'salida',
                'badge-amber':   m.type === 'ajuste',
              }">{{ m.type }}</span>
            </td>
            <td class="td-muted">{{ m.warehouse?.name }}</td>
            <td class="td-right td-bold">{{ m.quantity }}</td>
            <td class="td-right td-muted">{{ m.stock_before }}</td>
            <td class="td-right td-accent">{{ m.stock_after }}</td>
            <td class="td-muted td-sm">{{ m.reason || '—' }}</td>
            <td class="td-muted td-sm">{{ formatDate(m.created_at) }}</td>
          </tr>
          <tr v-if="movements.length === 0">
            <td colspan="8" class="td-empty">Sin movimientos registrados</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2 class="modal-title">Registrar movimiento</h2>
          <button class="modal-close" @click="showModal = false">
            <i class="ti ti-x" aria-hidden="true"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="field">
            <label class="field-label">Producto</label>
            <select v-model="form.product_id" class="field-input">
              <option v-for="p in products" :key="p.id" :value="p.id">
                {{ p.name }} — Stock: {{ p.stock }}
              </option>
            </select>
          </div>
          <div class="field">
            <label class="field-label">Bodega</label>
            <select v-model="form.warehouse_id" class="field-input">
              <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
            </select>
          </div>
          <div class="field-row">
            <div class="field">
              <label class="field-label">Tipo</label>
              <select v-model="form.type" class="field-input">
                <option value="entrada">Entrada</option>
                <option value="salida">Salida</option>
                <option value="ajuste">Ajuste</option>
              </select>
            </div>
            <div class="field">
              <label class="field-label">Cantidad</label>
              <input v-model="form.quantity" type="number" min="1" class="field-input" />
            </div>
          </div>
          <div class="field">
            <label class="field-label">Motivo (opcional)</label>
            <input v-model="form.reason" type="text" class="field-input" placeholder="Ej: Reposición de mercancía" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showModal = false">Cancelar</button>
          <button class="btn-primary" @click="saveMovement">Registrar movimiento</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import { useAuthStore } from '../stores/auth'
import api from '../api/axios'

const auth       = useAuthStore()
const movements  = ref([])
const products   = ref([])
const warehouses = ref([])
const showModal  = ref(false)
const form = ref({ product_id: '', warehouse_id: '', type: 'entrada', quantity: 1, reason: '' })

const today = new Date().toDateString()
const totalEntradas = computed(() => movements.value.filter(m => m.type === 'entrada' && new Date(m.created_at).toDateString() === today).length)
const totalSalidas  = computed(() => movements.value.filter(m => m.type === 'salida'  && new Date(m.created_at).toDateString() === today).length)
const totalAjustes  = computed(() => movements.value.filter(m => m.type === 'ajuste'  && new Date(m.created_at).toDateString() === today).length)

function formatDate(d) {
  return new Date(d).toLocaleString('es-MX', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })
}

async function saveMovement() {
  try {
    await api.post('/stock-movements', form.value)
    showModal.value = false
    await loadData()
  } catch (e) { alert(e.response?.data?.message || 'Error al registrar movimiento') }
}

async function loadData() {
  const [movRes, prodRes, wareRes] = await Promise.all([
    api.get('/stock-movements'),
    api.get('/products'),
    api.get('/warehouses'),
  ])
  movements.value  = movRes.data
  products.value   = prodRes.data
  warehouses.value = wareRes.data
}

onMounted(loadData)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap');
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/tabler-icons.min.css');

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; }

.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
.page-title { font-size: 22px; font-weight: 600; color: #F4F4F2; letter-spacing: -0.02em; }
.page-sub { font-size: 13px; color: #5C5C5A; margin-top: 2px; }

.btn-primary { display: flex; align-items: center; gap: 6px; background: #D97706; color: #fff; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; padding: 8px 14px; border: none; border-radius: 8px; cursor: pointer; transition: background 0.15s, transform 0.1s; }
.btn-primary:hover { background: #B45309; }
.btn-primary:active { transform: scale(0.97); }
.btn-primary .ti { font-size: 15px; }

.btn-ghost { background: none; border: 1px solid #2E2E30; color: #8C8C8A; font-family: 'DM Sans', sans-serif; font-size: 13px; padding: 8px 14px; border-radius: 8px; cursor: pointer; transition: all 0.12s; }
.btn-ghost:hover { border-color: #3E3E40; color: #C4C4C2; }

.stats-row {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

.stat-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  border: 1px solid;
}

.stat-pill .ti { font-size: 15px; }
.stat-pill-val { font-weight: 600; font-size: 15px; }
.stat-pill-label { color: inherit; opacity: 0.7; font-size: 12px; }

.stat-pill--green { background: #0C2218; border-color: #163D28; color: #34D399; }
.stat-pill--red   { background: #2A0A0A; border-color: #3D1414; color: #F87171; }
.stat-pill--amber { background: #2A1A06; border-color: #3D280A; color: #FBBF24; }

.table-wrap { background: #1E1E20; border: 1px solid #2A2A2C; border-radius: 10px; overflow: hidden; }

.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }

.data-table thead th {
  background: #222224;
  color: #6C6C6A;
  font-weight: 500;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 10px 14px;
  text-align: left;
  border-bottom: 1px solid #2A2A2C;
}

.th-right { text-align: right !important; }

.table-row { border-bottom: 1px solid #242426; transition: background 0.1s; }
.table-row:last-child { border-bottom: none; }
.table-row:hover { background: #222224; }

.data-table td { padding: 10px 14px; color: #C4C4C2; vertical-align: middle; }

.td-name { font-weight: 500; color: #E4E4E2; }
.td-muted { color: #6C6C6A; }
.td-right { text-align: right; }
.td-bold { font-weight: 600; color: #E4E4E2; }
.td-accent { color: #60A5FA; font-weight: 500; }
.td-sm { font-size: 12px; }

.badge-success { display: inline-flex; font-size: 11px; padding: 2px 8px; border-radius: 99px; background: #0C2218; color: #34D399; border: 1px solid #163D28; text-transform: capitalize; }
.badge-danger  { display: inline-flex; font-size: 11px; padding: 2px 8px; border-radius: 99px; background: #2A0A0A; color: #F87171; border: 1px solid #3D1414; text-transform: capitalize; }
.badge-amber   { display: inline-flex; font-size: 11px; padding: 2px 8px; border-radius: 99px; background: #2A1A06; color: #FBBF24; border: 1px solid #3D280A; text-transform: capitalize; }

.td-empty { text-align: center; padding: 32px !important; color: #4A4A48; font-size: 13px; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(2px); }

.modal { background: #1E1E20; border: 1px solid #2E2E30; border-radius: 12px; width: 100%; max-width: 440px; overflow: hidden; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid #2A2A2C; }
.modal-title { font-size: 15px; font-weight: 600; color: #F4F4F2; }
.modal-close { background: none; border: none; color: #5C5C5A; font-size: 18px; cursor: pointer; padding: 4px; border-radius: 6px; transition: color 0.12s; }
.modal-close:hover { color: #C4C4C2; }

.modal-body { padding: 20px; display: flex; flex-direction: column; gap: 14px; }

.field { display: flex; flex-direction: column; gap: 6px; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.field-label { font-size: 11px; font-weight: 500; color: #6C6C6A; text-transform: uppercase; letter-spacing: 0.06em; }
.field-input { background: #242426; border: 1px solid #2E2E30; color: #E4E4E2; font-family: 'DM Sans', sans-serif; font-size: 13px; padding: 9px 11px; border-radius: 7px; outline: none; transition: border-color 0.15s; }
.field-input:focus { border-color: #D97706; }
.field-input::placeholder { color: #4A4A48; }

.modal-footer { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 16px 20px; border-top: 1px solid #2A2A2C; }
</style>