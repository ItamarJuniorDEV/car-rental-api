<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated class="bg-primary">
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="drawer = !drawer" />
        <q-icon name="directions_car" size="22px" class="q-ml-sm q-mr-xs" />
        <q-toolbar-title class="text-weight-bold" style="letter-spacing: -0.3px">
          Car Rental
        </q-toolbar-title>
        <q-chip
          v-if="auth.isAdmin"
          dense
          color="white"
          text-color="primary"
          icon="shield"
          label="Admin"
          class="q-mr-sm"
          style="font-size: 11px"
        />
        <span class="text-caption q-mr-xs" style="opacity: .85">{{ auth.user?.name }}</span>
        <q-btn flat round dense icon="logout" @click="handleLogout">
          <q-tooltip>Sair</q-tooltip>
        </q-btn>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="drawer" show-if-above bordered>
      <div class="drawer-brand row items-center q-pa-md">
        <div class="drawer-brand-icon q-mr-sm">
          <q-icon name="directions_car" size="20px" color="white" />
        </div>
        <div>
          <div class="text-weight-bold text-primary" style="font-size: 15px; line-height: 1.2">Car Rental</div>
          <div class="text-grey-5" style="font-size: 11px">Gestão de frota</div>
        </div>
      </div>

      <q-separator />

      <q-list padding>
        <q-item-label header class="text-grey-5 text-caption" style="padding-top: 12px">
          GERAL
        </q-item-label>

        <q-item clickable :to="{ name: 'dashboard' }" active-class="menu-active">
          <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
          <q-item-section>Dashboard</q-item-section>
        </q-item>

        <q-item clickable :to="{ name: 'clients' }" active-class="menu-active">
          <q-item-section avatar><q-icon name="people" /></q-item-section>
          <q-item-section>Clientes</q-item-section>
        </q-item>

        <q-item clickable :to="{ name: 'rentals' }" active-class="menu-active">
          <q-item-section avatar><q-icon name="assignment" /></q-item-section>
          <q-item-section>Locações</q-item-section>
        </q-item>

        <template v-if="auth.isAdmin">
          <q-separator class="q-my-sm" />
          <q-item-label header class="text-grey-5 text-caption">
            ADMINISTRAÇÃO
          </q-item-label>

          <q-item clickable :to="{ name: 'brands' }" active-class="menu-active">
            <q-item-section avatar><q-icon name="local_offer" /></q-item-section>
            <q-item-section>Marcas</q-item-section>
          </q-item>

          <q-item clickable :to="{ name: 'lines' }" active-class="menu-active">
            <q-item-section avatar><q-icon name="category" /></q-item-section>
            <q-item-section>Linhas</q-item-section>
          </q-item>

          <q-item clickable :to="{ name: 'cars' }" active-class="menu-active">
            <q-item-section avatar><q-icon name="directions_car" /></q-item-section>
            <q-item-section>Veículos</q-item-section>
          </q-item>

          <q-item clickable :to="{ name: 'users' }" active-class="menu-active">
            <q-item-section avatar><q-icon name="manage_accounts" /></q-item-section>
            <q-item-section>Usuários</q-item-section>
          </q-item>
        </template>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from 'src/stores/auth'
import { authService } from 'src/services/auth.service'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const router = useRouter()
const auth = useAuthStore()
const drawer = ref(false)

async function handleLogout() {
  try {
    await authService.logout()
  } catch {
  } finally {
    auth.clear()
    router.push({ name: 'login' })
    $q.notify({ type: 'positive', message: 'Sessão encerrada.' })
  }
}
</script>

<style scoped>
.drawer-brand {
  min-height: 64px;
  border-bottom: 1px solid #f0f0f0;
}

.drawer-brand-icon {
  width: 32px;
  height: 32px;
  background: #1565c0;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.menu-active {
  background: #e3f2fd;
  color: #1565c0;
  border-radius: 8px;
}

.menu-active .q-icon {
  color: #1565c0;
}
</style>
