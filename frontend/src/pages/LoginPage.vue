<template>
  <div class="login-root">
    <div class="login-brand">
      <div class="brand-inner">
        <div class="brand-icon-wrap">
          <q-icon name="directions_car" size="56px" color="white" />
        </div>
        <div class="brand-title">Car Rental</div>
        <div class="brand-subtitle">Gestão completa da sua frota</div>

        <div class="brand-features">
          <div v-for="item in features" :key="item.text" class="brand-feature-item">
            <q-icon :name="item.icon" size="18px" color="white" class="q-mr-sm" style="opacity:.8" />
            <span>{{ item.text }}</span>
          </div>
        </div>
      </div>

      <div class="brand-footer">
        &copy; {{ new Date().getFullYear() }} Car Rental — Sistema de locação
      </div>
    </div>

    <div class="login-form-panel">
      <div class="form-inner">
        <div class="q-mb-xl">
          <div class="text-h5 text-weight-bold text-dark">Bem-vindo de volta</div>
          <div class="text-body2 text-grey-6 q-mt-xs">Faça login para continuar</div>
        </div>

        <q-form @submit.prevent="submit">
          <div class="q-mb-sm text-caption text-weight-medium text-grey-7">E-MAIL</div>
          <q-input
            v-model="email"
            type="email"
            outlined
            dense
            placeholder="seu@email.com"
            :rules="[val => !!val || 'Informe o e-mail']"
            class="q-mb-md"
            bg-color="grey-1"
          />

          <div class="q-mb-sm text-caption text-weight-medium text-grey-7">SENHA</div>
          <q-input
            v-model="password"
            :type="showPass ? 'text' : 'password'"
            outlined
            dense
            placeholder="••••••••"
            :rules="[val => !!val || 'Informe a senha']"
            class="q-mb-lg"
            bg-color="grey-1"
          >
            <template #append>
              <q-btn
                flat
                round
                dense
                :icon="showPass ? 'visibility_off' : 'visibility'"
                size="sm"
                color="grey-6"
                @click="showPass = !showPass"
              />
            </template>
          </q-input>

          <transition name="fade">
            <q-banner
              v-if="errorMsg"
              dense
              rounded
              class="bg-red-1 text-negative q-mb-md"
              style="border: 1px solid #ffcdd2"
            >
              <template #avatar>
                <q-icon name="error_outline" color="negative" />
              </template>
              {{ errorMsg }}
            </q-banner>
          </transition>

          <q-btn
            type="submit"
            label="Entrar"
            color="primary"
            unelevated
            class="full-width"
            size="md"
            padding="12px"
            :loading="loading"
          />
        </q-form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'src/stores/auth'
import { authService } from 'src/services/auth.service'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const showPass = ref(false)
const loading = ref(false)
const errorMsg = ref('')

const features = [
  { icon: 'inventory_2', text: 'Controle de frota em tempo real' },
  { icon: 'people', text: 'Gestão de clientes e contratos' },
  { icon: 'receipt_long', text: 'Locações com cálculo de multa' },
]

async function submit() {
  loading.value = true
  errorMsg.value = ''

  try {
    const { token } = await authService.login(email.value, password.value)
    auth.setAuth(token, null)
    const user = await authService.fetchMe()
    auth.setAuth(token, user)
    router.push({ name: 'dashboard' })
  } catch (err) {
    auth.clear()
    errorMsg.value =
      err.response?.data?.erro ||
      err.response?.data?.message ||
      'Credenciais inválidas. Tente novamente.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-root {
  display: flex;
  min-height: 100vh;
}

.login-brand {
  flex: 0 0 42%;
  background: linear-gradient(145deg, #1565c0 0%, #0d47a1 60%, #0a3880 100%);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 48px 52px;
  position: relative;
  overflow: hidden;
}

.login-brand::before {
  content: '';
  position: absolute;
  top: -120px;
  right: -120px;
  width: 360px;
  height: 360px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
}

.login-brand::after {
  content: '';
  position: absolute;
  bottom: -80px;
  left: -80px;
  width: 260px;
  height: 260px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.04);
}

.brand-inner {
  position: relative;
  z-index: 1;
}

.brand-icon-wrap {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 28px;
  backdrop-filter: blur(4px);
}

.brand-title {
  font-size: 32px;
  font-weight: 700;
  color: #fff;
  letter-spacing: -0.5px;
  margin-bottom: 8px;
}

.brand-subtitle {
  font-size: 16px;
  color: rgba(255, 255, 255, 0.65);
  margin-bottom: 52px;
}

.brand-features {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.brand-feature-item {
  display: flex;
  align-items: center;
  color: rgba(255, 255, 255, 0.85);
  font-size: 14px;
}

.brand-footer {
  position: relative;
  z-index: 1;
  color: rgba(255, 255, 255, 0.4);
  font-size: 12px;
}

.login-form-panel {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  padding: 48px 32px;
}

.form-inner {
  width: 100%;
  max-width: 380px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .login-brand {
    display: none;
  }

  .login-form-panel {
    padding: 40px 24px;
  }
}
</style>
