<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1 class="page-title">Productos</h1>
        <p class="page-sub">{{ products.length }} productos registrados</p>
      </div>
      <button v-if="auth.isAdmin" class="btn-primary" @click="openModal()">
        <i class="ti ti-plus" aria-hidden="true"></i>
        Nuevo producto
      </button>
    </div>

    <div class="toolbar">
      <div class="search-wrap">
        <i class="ti ti-search search-icon" aria-hidden="true"></i>
        <input v-model="search" type="text" class="search-input" placeholder="Buscar por nombre o SKU…" />
      </div>
      <div class="toolbar-meta">
        {{ filteredProducts.length }} resultado{{ filteredProducts.length !== 1 ? 's' : '' }}
      </div>
    </div>

    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>Producto</th>
            <th>SKU</th>
            <th>Categoría / Marca</th>
            <th class="th-right">Precio</th>
            <th class="th-right">Costo</th>
            <th class="th-right">Stock</th>
            <th class="th-center">Estado</th>
            <th v-if="auth.isAdmin" class="th-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in filteredProducts" :key="p.id" class="table-row">
            <td class="td-name">{{ p.name }}</td>
            <td class="td-mono">{{ p.sku }}</td>
            <td>
              <span class="td-cat">{{ p.category?.name }}</span>
              <span v-if="p.category?.brand" class="brand-chip">{{ p.category.brand }}</span>
            </td>
            <td class="td-right td-price">${{ Number(p.price).toFixed(2) }}</td>
            <td class="td-right td-muted">${{ Number(p.cost).toFixed(2) }}</td>
            <td class="td-right">
              <span :class="p.stock <= p.stock_min ? 'stock-low' : 'stock-ok'">{{ p.stock }}</span>
            </td>
            <td class="td-center">
              <span :class="p.stock <= p.stock_min ? 'badge-danger' : 'badge-success'">
                {{ p.stock <= p.stock_min ? 'Stock bajo' : 'OK' }}
              </span>
            </td>
            <td v-if="auth.isAdmin" class="td-center">
              <button class="action-btn" @click="openModal(p)">
                <i class="ti ti-edit" aria-hidden="true"></i>
              </button>
              <button class="action-btn action-btn--danger" @click="deleteProduct(p)">
                <i class="ti ti-trash" aria-hidden="true"></i>
              </button>
            </td>
          </tr>
          <tr v-if="filteredProducts.length === 0">
            <td :colspan="auth.isAdmin ? 8 : 7" class="td-empty">
              Sin resultados para "{{ search }}"
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2 class="modal-title">{{ editingProduct ? 'Editar producto' : 'Nuevo producto' }}</h2>
          <button class="modal-close" @click="showModal = false">
            <i class="ti ti-x" aria-hidden="true"></i>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field field--full">
              <label class="field-label">Nombre</label>
              <input v-model="form.name" type="text" class="field-input" />
            </div>
            <div class="field">
              <label class="field-label">SKU</label>
              <input v-model="form.sku" type="text" class="field-input" />
            </div>
            <div class="field">
              <label class="field-label">Categoría</label>
              <select v-model="form.category_id" class="field-input">
                <option v-for="c in categories" :key="c.id" :value="c.id">
                  {{ c.name }}{{ c.brand ? ' · ' + c.brand : '' }}
                </option>
              </select>
            </div>
            <div class="field">
              <label class="field-label">Precio de venta</label>
              <input v-model="form.price" type="number" class="field-input" />
            </div>
            <div class="field">
              <label class="field-label">Costo</label>
              <input v-model="form.cost" type="number" class="field-input" />
            </div>
            <div class="field">
              <label class="field-label">Stock actual</label>
              <input v-model="form.stock" type="number" class="field-input" />
            </div>
            <div class="field">
              <label class="field-label">Stock mínimo</label>
              <input v-model="form.stock_min" type="number" class="field-input" />
            </div>
            <div class="field">
              <label class="field-label">Unidad</label>
              <select v-model="form.unit" class="field-input">
                <option>pieza</option>
                <option>kg</option>
                <option>litro</option>
                <option>metro</option>
                <option>caja</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showModal = false">Cancelar</button>
          <button class="btn-primary" @click="saveProduct">
            {{ editingProduct ? 'Guardar cambios' : 'Crear producto' }}
          </button>
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

const auth           = useAuthStore()
const products       = ref([])
const categories     = ref([])
const search         = ref('')
const showModal      = ref(false)
const editingProduct = ref(null)
const form = ref({ name: '', sku: '', category_id: '', price: '', cost: '', stock: '', stock_min: 5, unit: 'pieza' })

const filteredProducts = computed(() =>
  products.value.filter(p =>
    p.name.toLowerCase().includes(search.value.toLowerCase()) ||
    p.sku.toLowerCase().includes(search.value.toLowerCase())
  )
)

function openModal(product = null) {
  editingProduct.value = product
  form.value = product
    ? { ...product, category_id: product.category_id }
    : { name: '', sku: '', category_id: categories.value[0]?.id, price: '', cost: '', stock: '', stock_min: 5, unit: 'pieza' }
  showModal.value = true
}

async function saveProduct() {
  try {
    if (editingProduct.value) await api.put(`/products/${editingProduct.value.id}`, form.value)
    else await api.post('/products', form.value)
    showModal.value = false
    await load()
  } catch (e) { alert(e.response?.data?.message || 'Error al guardar') }
}

async function deleteProduct(p) {
  if (!confirm(`¿Desactivar "${p.name}"?`)) return
  await api.delete(`/products/${p.id}`)
  await load()
}

async function load() {
  const [prodRes, catRes] = await Promise.all([api.get('/products'), api.get('/categories')])
  products.value   = prodRes.data
  categories.value = catRes.data
}

onMounted(load)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap');
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/tabler-icons.min.css');

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; }

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 24px;
}

.page-title {
  font-size: 22px;
  font-weight: 600;
  color: #F4F4F2;
  letter-spacing: -0.02em;
}

.page-sub { font-size: 13px; color: #5C5C5A; margin-top: 2px; }

.btn-primary {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #D97706;
  color: #fff;
  font-family: 'DM Sans', sans-serif;
  font-size: 13px;
  font-weight: 500;
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s, transform 0.1s;
  white-space: nowrap;
}

.btn-primary:hover { background: #B45309; }
.btn-primary:active { transform: scale(0.97); }
.btn-primary .ti { font-size: 15px; }

.btn-ghost {
  background: none;
  border: 1px solid #2E2E30;
  color: #8C8C8A;
  font-family: 'DM Sans', sans-serif;
  font-size: 13px;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.12s;
}

.btn-ghost:hover { border-color: #3E3E40; color: #C4C4C2; }

.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.search-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 10px;
  font-size: 15px;
  color: #5C5C5A;
  pointer-events: none;
}

.search-input {
  background: #1E1E20;
  border: 1px solid #2A2A2C;
  color: #E4E4E2;
  font-family: 'DM Sans', sans-serif;
  font-size: 13px;
  padding: 8px 12px 8px 34px;
  border-radius: 8px;
  width: 280px;
  outline: none;
  transition: border-color 0.15s;
}

.search-input::placeholder { color: #4A4A48; }
.search-input:focus { border-color: #D97706; }

.toolbar-meta { font-size: 12px; color: #5C5C5A; }

.table-wrap {
  background: #1E1E20;
  border: 1px solid #2A2A2C;
  border-radius: 10px;
  overflow: hidden;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

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
.th-center { text-align: center !important; }

.table-row {
  border-bottom: 1px solid #242426;
  transition: background 0.1s;
}

.table-row:last-child { border-bottom: none; }
.table-row:hover { background: #222224; }

.data-table td {
  padding: 10px 14px;
  color: #C4C4C2;
  vertical-align: middle;
}

.td-name { font-weight: 500; color: #E4E4E2; }
.td-mono { font-family: monospace; font-size: 11px; color: #6C6C6A; }
.td-right { text-align: right; }
.td-center { text-align: center; }
.td-price { color: #34D399; font-weight: 500; }
.td-muted { color: #5C5C5A; }

.td-cat { display: block; font-size: 12px; color: #9C9C9A; }

.brand-chip {
  display: inline-block;
  margin-top: 2px;
  font-size: 10px;
  background: #1A1A2E;
  color: #60A5FA;
  border: 1px solid #1E2A3D;
  padding: 1px 6px;
  border-radius: 99px;
}

.stock-low { color: #F87171; font-weight: 600; }
.stock-ok  { color: #E4E4E2; }

.badge-success {
  display: inline-flex;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 99px;
  background: #0C2218;
  color: #34D399;
  border: 1px solid #163D28;
}

.badge-danger {
  display: inline-flex;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 99px;
  background: #2A0A0A;
  color: #F87171;
  border: 1px solid #3D1414;
}

.action-btn {
  background: none;
  border: none;
  color: #5C5C5A;
  font-size: 15px;
  cursor: pointer;
  padding: 4px 6px;
  border-radius: 6px;
  transition: all 0.12s;
}

.action-btn:hover { background: #2A2A2C; color: #C4C4C2; }
.action-btn--danger:hover { background: #2A0A0A; color: #F87171; }

.td-empty {
  text-align: center;
  padding: 32px !important;
  color: #4A4A48;
  font-size: 13px;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  backdrop-filter: blur(2px);
}

.modal {
  background: #1E1E20;
  border: 1px solid #2E2E30;
  border-radius: 12px;
  width: 100%;
  max-width: 520px;
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 20px;
  border-bottom: 1px solid #2A2A2C;
}

.modal-title {
  font-size: 15px;
  font-weight: 600;
  color: #F4F4F2;
}

.modal-close {
  background: none;
  border: none;
  color: #5C5C5A;
  font-size: 18px;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  transition: color 0.12s;
}

.modal-close:hover { color: #C4C4C2; }

.modal-body { padding: 20px; }

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.field { display: flex; flex-direction: column; gap: 6px; }
.field--full { grid-column: 1 / -1; }

.field-label {
  font-size: 11px;
  font-weight: 500;
  color: #6C6C6A;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.field-input {
  background: #242426;
  border: 1px solid #2E2E30;
  color: #E4E4E2;
  font-family: 'DM Sans', sans-serif;
  font-size: 13px;
  padding: 9px 11px;
  border-radius: 7px;
  outline: none;
  transition: border-color 0.15s;
}

.field-input:focus { border-color: #D97706; }

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 20px;
  border-top: 1px solid #2A2A2C;
}
</style>