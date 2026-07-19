<template>
  <AppLayout>
    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">{{ today }}</p>
      </div>
    </div>

    <div class="stats-grid">
      <div v-for="stat in stats" :key="stat.label" class="stat-card">
        <div class="stat-accent"></div>
        <div class="stat-body">
          <div class="stat-icon-wrap" :class="`stat-icon--${stat.color}`">
            <i :class="`ti ${stat.icon}`" aria-hidden="true"></i>
          </div>
          <div class="stat-info">
            <span class="stat-value">{{ stat.value }}</span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="section-header">
      <span class="section-title">Stock bajo</span>
      <span v-if="lowStock.length" class="section-badge">{{ lowStock.length }} productos</span>
    </div>

    <div v-if="lowStock.length === 0" class="empty-state">
      <i class="ti ti-circle-check" aria-hidden="true"></i>
      <span>Todo el inventario está al día</span>
    </div>

    <div v-else class="alert-list">
      <div v-for="p in lowStock" :key="p.id" class="alert-row">
        <div class="alert-left">
          <span class="alert-dot"></span>
          <div>
            <span class="alert-name">{{ p.name }}</span>
            <span class="alert-sku">{{ p.sku }}</span>
          </div>
        </div>
        <div class="alert-right">
          <span class="alert-stock">{{ p.stock }} uds</span>
          <span class="alert-min">mín {{ p.stock_min }}</span>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '../components/AppLayout.vue'
import api from '../api/axios'

const lowStock = ref([])
const today = new Date().toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })

const stats = ref([
  { icon: 'ti-currency-dollar', color: 'green',  value: '$0.00', label: 'Ventas hoy'     },
  { icon: 'ti-box',             color: 'blue',   value: '0',     label: 'Productos'      },
  { icon: 'ti-receipt',         color: 'amber',  value: '0',     label: 'Ventas del día' },
  { icon: 'ti-alert-triangle',  color: 'red',    value: '0',     label: 'Stock bajo'     },
])

onMounted(async () => {
  try {
    const [prodRes, lowRes, todayRes] = await Promise.all([
      api.get('/products'),
      api.get('/products/low-stock'),
      api.get('/sales/today'),
    ])
    lowStock.value = lowRes.data
    stats.value = [
      { icon: 'ti-currency-dollar', color: 'green', value: '$' + Number(todayRes.data.total_ingresos).toFixed(2), label: 'Ventas hoy'     },
      { icon: 'ti-box',             color: 'blue',  value: prodRes.data.length,                                    label: 'Productos'      },
      { icon: 'ti-receipt',         color: 'amber', value: todayRes.data.total_ventas,                              label: 'Ventas del día' },
      { icon: 'ti-alert-triangle',  color: 'red',   value: lowRes.data.length,                                      label: 'Stock bajo'     },
    ]
  } catch (e) { console.error(e) }
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap');
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/tabler-icons.min.css');

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; }

.page-header {
  margin-bottom: 28px;
}

.page-title {
  font-size: 22px;
  font-weight: 600;
  color: #F4F4F2;
  letter-spacing: -0.02em;
}

.page-sub {
  font-size: 13px;
  color: #5C5C5A;
  margin-top: 2px;
  text-transform: capitalize;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 32px;
}

.stat-card {
  background: #1E1E20;
  border: 1px solid #2A2A2C;
  border-radius: 10px;
  overflow: hidden;
  display: flex;
  transition: border-color 0.15s;
}

.stat-card:hover { border-color: #3A3A3C; }

.stat-accent {
  width: 3px;
  background: #D97706;
  flex-shrink: 0;
}

.stat-body {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  flex: 1;
}

.stat-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 17px;
  flex-shrink: 0;
}

.stat-icon--green  { background: #0F2A18; color: #34D399; }
.stat-icon--blue   { background: #0A1E38; color: #60A5FA; }
.stat-icon--amber  { background: #2A1A06; color: #FBBF24; }
.stat-icon--red    { background: #2A0A0A; color: #F87171; }

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.stat-value {
  font-size: 20px;
  font-weight: 600;
  color: #F4F4F2;
  letter-spacing: -0.02em;
  line-height: 1;
}

.stat-label {
  font-size: 12px;
  color: #6C6C6A;
}

.section-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
}

.section-title {
  font-size: 13px;
  font-weight: 500;
  color: #9C9C9A;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.section-badge {
  font-size: 11px;
  background: #2A1206;
  color: #F87171;
  border: 1px solid #3D1A0A;
  padding: 1px 7px;
  border-radius: 99px;
}

.empty-state {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 20px;
  background: #1E1E20;
  border: 1px solid #2A2A2C;
  border-radius: 10px;
  color: #5C5C5A;
  font-size: 13px;
}

.empty-state .ti { font-size: 16px; color: #34D399; }

.alert-list {
  background: #1E1E20;
  border: 1px solid #2A2A2C;
  border-radius: 10px;
  overflow: hidden;
}

.alert-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #242426;
  transition: background 0.1s;
}

.alert-row:last-child { border-bottom: none; }
.alert-row:hover { background: #222224; }

.alert-left {
  display: flex;
  align-items: center;
  gap: 10px;
}

.alert-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #F87171;
  flex-shrink: 0;
}

.alert-name {
  font-size: 13px;
  font-weight: 500;
  color: #D4D4D2;
  display: block;
}

.alert-sku {
  font-size: 11px;
  color: #5C5C5A;
  font-family: monospace;
}

.alert-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.alert-stock {
  font-size: 14px;
  font-weight: 600;
  color: #F87171;
}

.alert-min {
  font-size: 11px;
  color: #5C5C5A;
}
</style>