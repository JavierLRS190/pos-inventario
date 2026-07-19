<template>
  <div class="login-shell">
    <div class="login-card">
      <div class="login-header">
        <div class="login-logo">
          <i class="ti ti-building-store" aria-hidden="true"></i>
        </div>
        <h1 class="login-title">POS Inventario</h1>
        <p class="login-sub">Accede a tu cuenta para continuar</p>
      </div>

      <div v-if="error" class="error-bar">
        <i class="ti ti-alert-circle" aria-hidden="true"></i>
        {{ error }}
      </div>

      <div class="form-group">
        <label class="form-label">Correo electrónico</label>
        <input
          v-model="email"
          type="email"
          class="form-input"
          placeholder="usuario@empresa.com"
          @keyup.enter="handleLogin"
        />
      </div>

      <div class="form-group">
        <label class="form-label">Contraseña</label>
        <input
          v-model="password"
          type="password"
          class="form-input"
          placeholder="••••••••"
          @keyup.enter="handleLogin"
        />
      </div>

      <button class="submit-btn" :disabled="loading" @click="handleLogin">
        <i v-if="!loading" class="ti ti-arrow-right" aria-hidden="true"></i>
        <span>{{ loading ? 'Verificando...' : 'Iniciar sesión' }}</span>
      </button>

      <div class="divider">
        <span>acceso rápido</span>
      </div>

      <div class="quick-access">
        <button
          v-for="u in testUsers"
          :key="u.role"
          class="quick-btn"
          @click="fillUser(u)"
        >
          <span class="quick-role">{{ u.role }}</span>
          <span class="quick-email">{{ u.email }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const email    = ref('')
const password = ref('')
const loading  = ref(false)
const error    = ref('')

const testUsers = [
  { role: 'Admin',     email: 'admin@pos.com',     password: 'admin1234'     },
  { role: 'Cajero',    email: 'cajero@pos.com',    password: 'cajero1234'    },
  { role: 'Bodeguero', email: 'bodeguero@pos.com', password: 'bodeguero1234' },
]

function fillUser(u) {
  email.value    = u.email
  password.value = u.password
}

async function handleLogin() {
  if (!email.value || !password.value) { error.value = 'Completa todos los campos'; return }
  loading.value = true
  error.value   = ''
  try {
    await auth.login(email.value, password.value)
    router.push('/')
  } catch {
    error.value = 'Correo o contraseña incorrectos'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap');
@import url('https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.19.0/tabler-icons.min.css');

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'DM Sans', sans-serif; }

.login-shell {
  min-height: 100vh;
  background: #18181A;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
}

.login-card {
  background: #1E1E20;
  border: 1px solid #2A2A2C;
  border-radius: 14px;
  padding: 36px;
  width: 100%;
  max-width: 380px;
}

.login-header {
  text-align: center;
  margin-bottom: 28px;
}

.login-logo {
  width: 48px;
  height: 48px;
  background: #D97706;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  color: #fff;
  margin: 0 auto 16px;
}

.login-title {
  font-size: 20px;
  font-weight: 600;
  color: #F4F4F2;
  letter-spacing: -0.02em;
}

.login-sub {
  font-size: 13px;
  color: #6C6C6A;
  margin-top: 4px;
}

.error-bar {
  display: flex;
  align-items: center;
  gap: 7px;
  background: #2A0A0A;
  border: 1px solid #3D1414;
  color: #F87171;
  font-size: 13px;
  padding: 10px 12px;
  border-radius: 8px;
  margin-bottom: 16px;
}

.error-bar .ti { font-size: 15px; flex-shrink: 0; }

.form-group { margin-bottom: 14px; }

.form-label {
  display: block;
  font-size: 12px;
  font-weight: 500;
  color: #8C8C8A;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.form-input {
  width: 100%;
  background: #242426;
  border: 1px solid #2E2E30;
  color: #E4E4E2;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  padding: 10px 12px;
  border-radius: 8px;
  outline: none;
  transition: border-color 0.15s;
}

.form-input::placeholder { color: #4A4A48; }
.form-input:focus { border-color: #D97706; }

.submit-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: #D97706;
  color: #fff;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  font-weight: 500;
  padding: 11px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 20px;
  transition: background 0.15s, transform 0.1s;
}

.submit-btn:hover:not(:disabled) { background: #B45309; }
.submit-btn:active:not(:disabled) { transform: scale(0.98); }
.submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.submit-btn .ti { font-size: 16px; }

.divider {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 24px 0 14px;
}

.divider::before,
.divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #2A2A2C;
}

.divider span {
  font-size: 11px;
  color: #4A4A48;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  white-space: nowrap;
}

.quick-access {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.quick-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #242426;
  border: 1px solid #2E2E30;
  border-radius: 8px;
  padding: 9px 12px;
  cursor: pointer;
  transition: all 0.12s;
}

.quick-btn:hover {
  border-color: #3E3E40;
  background: #2A2A2C;
}

.quick-role {
  font-size: 13px;
  font-weight: 500;
  color: #D4D4D2;
  font-family: 'DM Sans', sans-serif;
}

.quick-email {
  font-size: 12px;
  color: #5C5C5A;
  font-family: monospace;
}
</style>