<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1 class="page-title">Categorías</h1>
        <p class="page-sub">{{ categories.length }} categorías registradas</p>
      </div>
      <button v-if="auth.isAdmin" class="btn-primary" @click="openModal()">
        <i class="ti ti-plus" aria-hidden="true"></i>
        Nueva categoría
      </button>
    </div>

    <div class="toolbar">
      <div class="search-wrap">
        <i class="ti ti-search search-icon" aria-hidden="true"></i>
        <input v-model="search" type="text" class="search-input" placeholder="Buscar por nombre o marca…" />
      </div>
    </div>

    <div class="cat-grid">
      <div v-for="c in filteredCategories" :key="c.id" class="cat-card">
        <div class="cat-card-top">
          <div class="cat-icon">
            <i class="ti ti-tag" aria-hidden="true"></i>
          </div>
          <div v-if="auth.isAdmin" class="cat-actions">
            <button class="action-btn" @click="openModal(c)"><i class="ti ti-edit"></i></button>
            <button class="action-btn action-btn--danger" @click="deleteCategory(c)"><i class="ti ti-trash"></i></button>
          </div>
        </div>
        <div class="cat-name">{{ c.name }}</div>
        <div v-if="c.brand" class="cat-brand">
          <i class="ti ti-building-factory" aria-hidden="true"></i>
          {{ c.brand }}
        </div>
        <div v-if="c.description" class="cat-desc">{{ c.description }}</div>
      </div>

      <div v-if="filteredCategories.length === 0" class="cat-empty">
        Sin resultados para "{{ search }}"
      </div>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal">
        <div class="modal-header">
          <h2 class="modal-title">{{ editing ? 'Editar categoría' : 'Nueva categoría' }}</h2>
          <button class="modal-close" @click="showModal = false"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
          <div class="field">
            <label class="field-label">Nombre</label>
            <input v-model="form.name" type="text" class="field-input" />
          </div>
          <div class="field" style="margin-top:14px">
            <label class="field-label">Marca</label>
            <input v-model="form.brand" type="text" class="field-input" placeholder="Ej: Truper, Urrea, 3M…" />
          </div>
          <div class="field" style="margin-top:14px">
            <label class="field-label">Descripción (opcional)</label>
            <input v-model="form.description" type="text" class="field-input" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-ghost" @click="showModal = false">Cancelar</button>
          <button class="btn-primary" @click="save">{{ editing ? 'Guardar cambios' : 'Crear categoría' }}</button>
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
const categories = ref([])
const search     = ref('')
const showModal  = ref(false)
const editing    = ref(null)
const form = ref({ name: '', brand: '', description: '' })

const filteredCategories = computed(() =>
  categories.value.filter(c =>
    c.name.toLowerCase().includes(search.value.toLowerCase()) ||
    (c.brand && c.brand.toLowerCase().includes(search.value.toLowerCase()))
  )
)

function openModal(category = null) {
  editing.value   = category
  form.value      = category ? { name: category.name, brand: category.brand || '', description: category.description || '' } : { name: '', brand: '', description: '' }
  showModal.value = true
}

async function save() {
  try {
    if (editing.value) await api.put(`/categories/${editing.value.id}`, form.value)
    else await api.post('/categories', form.value)
    showModal.value = false
    await load()
  } catch (e) { alert(e.response?.data?.message || 'Error al guardar') }
}

async function deleteCategory(c) {
  if (!confirm(`¿Desactivar "${c.name}"?`)) return
  await api.delete(`/categories/${c.id}`)
  await load()
}

async function load() {
  const res = await api.get('/categories')
  categories.value = res.data
}

onMounted(load)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap');
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/tabler-icons.min.css');

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; }

.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; }
.page-title { font-size: 22px; font-weight: 600; color: #F4F4F2; letter-spacing: -0.02em; }
.page-sub { font-size: 13px; color: #5C5C5A; margin-top: 2px; }

.btn-primary { display: flex; align-items: center; gap: 6px; background: #D97706; color: #fff; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; padding: 8px 14px; border: none; border-radius: 8px; cursor: pointer; transition: background 0.15s, transform 0.1s; white-space: nowrap; }
.btn-primary:hover { background: #B45309; }
.btn-primary:active { transform: scale(0.97); }
.btn-primary .ti { font-size: 15px; }

.btn-ghost { background: none; border: 1px solid #2E2E30; color: #8C8C8A; font-family: 'DM Sans', sans-serif; font-size: 13px; padding: 8px 14px; border-radius: 8px; cursor: pointer; transition: all 0.12s; }
.btn-ghost:hover { border-color: #3E3E40; color: #C4C4C2; }

.toolbar { margin-bottom: 16px; }
.search-wrap { position: relative; display: inline-flex; align-items: center; }
.search-icon { position: absolute; left: 10px; font-size: 15px; color: #5C5C5A; pointer-events: none; }
.search-input { background: #1E1E20; border: 1px solid #2A2A2C; color: #E4E4E2; font-family: 'DM Sans', sans-serif; font-size: 13px; padding: 8px 12px 8px 34px; border-radius: 8px; width: 280px; outline: none; transition: border-color 0.15s; }
.search-input::placeholder { color: #4A4A48; }
.search-input:focus { border-color: #D97706; }

.cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; }

.cat-card { background: #1E1E20; border: 1px solid #2A2A2C; border-radius: 10px; padding: 16px; transition: border-color 0.15s; }
.cat-card:hover { border-color: #3A3A3C; }

.cat-card-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }

.cat-icon { width: 32px; height: 32px; background: #292116; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 15px; color: #D97706; }

.cat-actions { display: flex; gap: 4px; opacity: 0; transition: opacity 0.15s; }
.cat-card:hover .cat-actions { opacity: 1; }

.action-btn { background: none; border: none; color: #5C5C5A; font-size: 14px; cursor: pointer; padding: 4px; border-radius: 5px; transition: all 0.12s; }
.action-btn:hover { background: #2A2A2C; color: #C4C4C2; }
.action-btn--danger:hover { background: #2A0A0A; color: #F87171; }

.cat-name { font-size: 14px; font-weight: 500; color: #E4E4E2; margin-bottom: 6px; }

.cat-brand { display: flex; align-items: center; gap: 5px; font-size: 12px; color: #60A5FA; background: #0A1E38; border: 1px solid #1E2A3D; padding: 3px 8px; border-radius: 99px; display: inline-flex; margin-bottom: 6px; }
.cat-brand .ti { font-size: 11px; }

.cat-desc { font-size: 12px; color: #5C5C5A; line-height: 1.5; }

.cat-empty { grid-column: 1 / -1; text-align: center; padding: 48px; color: #4A4A48; font-size: 13px; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(2px); }
.modal { background: #1E1E20; border: 1px solid #2E2E30; border-radius: 12px; width: 100%; max-width: 400px; overflow: hidden; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid #2A2A2C; }
.modal-title { font-size: 15px; font-weight: 600; color: #F4F4F2; }
.modal-close { background: none; border: none; color: #5C5C5A; font-size: 18px; cursor: pointer; padding: 4px; border-radius: 6px; }
.modal-close:hover { color: #C4C4C2; }
.modal-body { padding: 20px; }
.field { display: flex; flex-direction: column; gap: 6px; }
.field-label { font-size: 11px; font-weight: 500; color: #6C6C6A; text-transform: uppercase; letter-spacing: 0.06em; }
.field-input { background: #242426; border: 1px solid #2E2E30; color: #E4E4E2; font-family: 'DM Sans', sans-serif; font-size: 13px; padding: 9px 11px; border-radius: 7px; outline: none; transition: border-color 0.15s; }
.field-input:focus { border-color: #D97706; }
.field-input::placeholder { color: #4A4A48; }
.modal-footer { display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 16px 20px; border-top: 1px solid #2A2A2C; }
</style>