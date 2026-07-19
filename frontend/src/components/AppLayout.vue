<template>
  <div class="app-shell">
    <nav class="topbar">
      <div class="topbar-brand">
        <div class="brand-icon">
          <i class="ti ti-building-store"></i>
        </div>
        <span class="brand-name">POS Inventario</span>
      </div>
      <div class="topbar-right">
        <span class="user-name">{{ auth.user?.name }}</span>
        <span class="role-badge">{{ auth.user?.roles?.[0] }}</span>
        <button class="logout-btn" @click="handleLogout">
          <i class="ti ti-logout"></i>
          Salir
        </button>
      </div>
    </nav>

    <div class="layout">
      <aside class="sidebar">
        <nav class="sidebar-nav">
          <RouterLink
            v-for="item in menuItems"
            :key="item.path"
            :to="item.path"
            class="nav-item"
            active-class="nav-item--active"
          >
            <i :class="`ti ${item.icon}`" aria-hidden="true"></i>
            <span>{{ item.label }}</span>
          </RouterLink>
        </nav>
        <div class="sidebar-footer">
          <span class="sidebar-version">v1.0</span>
        </div>
      </aside>

      <main class="main-content">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const menuItems = [
  { path: '/',           icon: 'ti-layout-dashboard', label: 'Dashboard'  },
  { path: '/products',   icon: 'ti-box',              label: 'Productos'  },
  { path: '/categories', icon: 'ti-tag',              label: 'Categorías' },
  { path: '/sales',      icon: 'ti-shopping-cart',    label: 'Ventas'     },
  { path: '/inventory',  icon: 'ti-building-warehouse', label: 'Bodega'   },
]

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap');
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/tabler-icons.min.css');

* { box-sizing: border-box; margin: 0; padding: 0; }

.app-shell {
  font-family: 'DM Sans', system-ui, sans-serif;
  background: #18181A;
  min-height: 100vh;
  color: #E4E4E2;
}

.topbar {
  height: 52px;
  background: #1E1E20;
  border-bottom: 1px solid #2A2A2C;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 20px 0 16px;
  position: sticky;
  top: 0;
  z-index: 40;
}

.topbar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
}

.brand-icon {
  width: 30px;
  height: 30px;
  background: #D97706;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  color: #fff;
}

.brand-name {
  font-size: 14px;
  font-weight: 600;
  color: #F4F4F2;
  letter-spacing: -0.01em;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-name {
  font-size: 13px;
  color: #9C9C9A;
}

.role-badge {
  font-size: 11px;
  font-weight: 500;
  background: #292116;
  color: #D97706;
  border: 1px solid #3D2E0E;
  padding: 2px 8px;
  border-radius: 99px;
  text-transform: capitalize;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 5px;
  background: none;
  border: 1px solid #2E2E30;
  color: #7A7A78;
  font-size: 12px;
  font-family: 'DM Sans', sans-serif;
  padding: 5px 10px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s;
}

.logout-btn:hover {
  color: #E4E4E2;
  border-color: #3E3E40;
  background: #242426;
}

.logout-btn .ti { font-size: 14px; }

.layout {
  display: flex;
  height: calc(100vh - 52px);
}

.sidebar {
  width: 200px;
  background: #1E1E20;
  border-right: 1px solid #2A2A2C;
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  padding: 12px 8px;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 8px 10px;
  border-radius: 7px;
  font-size: 13px;
  color: #7A7A78;
  text-decoration: none;
  transition: all 0.12s;
}

.nav-item .ti {
  font-size: 16px;
  flex-shrink: 0;
}

.nav-item:hover {
  background: #242426;
  color: #C4C4C2;
}

.nav-item--active {
  background: #242426;
  color: #E4E4E2;
}

.nav-item--active .ti {
  color: #D97706;
}

.sidebar-footer {
  padding: 8px 10px;
}

.sidebar-version {
  font-size: 11px;
  color: #4A4A48;
}

.main-content {
  flex: 1;
  overflow-y: auto;
  padding: 28px 32px;
}
</style>