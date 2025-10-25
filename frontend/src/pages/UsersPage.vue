<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <q-icon name="manage_accounts" size="28px" color="primary" class="q-mr-sm" />
      <div class="text-h5 text-weight-bold">Usuários</div>
      <q-space />
      <q-input
        v-model="search"
        dense
        outlined
        placeholder="Buscar..."
        clearable
        class="q-mr-sm"
        style="max-width: 220px"
      >
        <template #prepend><q-icon name="search" /></template>
      </q-input>
      <q-btn icon="add" label="Novo usuário" color="primary" unelevated @click="openForm()" />
    </div>

    <q-table
      :rows="users"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :filter="search"
      flat
      bordered
      no-data-label="Nenhum usuário encontrado"
      :rows-per-page-options="[10, 20, 0]"
    >
      <template #body-cell-role="props">
        <q-td :props="props">
          <q-badge :color="props.row.role === 'admin' ? 'primary' : 'grey-6'">
            {{ props.row.role === 'admin' ? 'Admin' : 'Usuário' }}
          </q-badge>
        </q-td>
      </template>
      <template #body-cell-actions="props">
        <q-td :props="props">
          <template v-if="props.row.id !== authStore.user?.id">
            <q-btn
              v-if="props.row.role !== 'admin'"
              flat
              dense
              size="sm"
              icon="shield"
              color="primary"
              label="Tornar admin"
              @click="changeRole(props.row, 'admin')"
            />
            <q-btn
              v-else
              flat
              dense
              size="sm"
              icon="person"
              color="grey-7"
              label="Revogar admin"
              @click="changeRole(props.row, 'user')"
            />
          </template>
          <span v-else class="text-grey-5 text-caption">você</span>
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="dialog">
      <q-card style="min-width: 380px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Novo usuário</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit.prevent="save">
            <q-input
              v-model="form.name"
              label="Nome"
              outlined
              autofocus
              :rules="[val => !!val || 'Informe o nome']"
              class="q-mb-sm"
            />
            <q-input
              v-model="form.email"
              label="E-mail"
              type="email"
              outlined
              :rules="[val => !!val || 'Informe o e-mail']"
              class="q-mb-sm"
            />
            <q-input
              v-model="form.password"
              label="Senha"
              type="password"
              outlined
              :rules="[val => (val && val.length >= 6) || 'Mínimo 6 caracteres']"
              class="q-mb-sm"
            />
            <q-select
              v-model="form.role"
              :options="roleOptions"
              option-value="value"
              option-label="label"
              emit-value
              map-options
              label="Perfil"
              outlined
              class="q-mb-md"
            />
            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn type="submit" label="Criar" color="primary" unelevated :loading="saving" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { userService } from 'src/services/user.service'
import { useAuthStore } from 'src/stores/auth'

const $q = useQuasar()
const authStore = useAuthStore()
const users = ref([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const search = ref('')
const form = ref({ name: '', email: '', password: '', role: 'user' })

const roleOptions = [
  { value: 'user', label: 'Usuário' },
  { value: 'admin', label: 'Admin' },
]

const columns = [
  { name: 'name', label: 'Nome', field: 'name', align: 'left', sortable: true },
  { name: 'email', label: 'E-mail', field: 'email', align: 'left' },
  { name: 'role', label: 'Perfil', field: 'role', align: 'left' },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'right' },
]

async function loadUsers() {
  loading.value = true
  try {
    users.value = await userService.list()
  } finally {
    loading.value = false
  }
}

function openForm() {
  form.value = { name: '', email: '', password: '', role: 'user' }
  dialog.value = true
}

async function save() {
  saving.value = true
  try {
    const created = await userService.create(form.value)
    users.value.push(created)
    users.value.sort((a, b) => a.name.localeCompare(b.name))
    dialog.value = false
    $q.notify({ type: 'positive', message: `Usuário ${created.name} criado.` })
  } catch (err) {
    $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao criar usuário.' })
  } finally {
    saving.value = false
  }
}

async function changeRole(user, role) {
  try {
    const updated = await userService.updateRole(user.id, role)
    const idx = users.value.findIndex(u => u.id === user.id)
    if (idx !== -1) users.value[idx] = { ...users.value[idx], role: updated.role }
    $q.notify({ type: 'positive', message: `Perfil de ${user.name} atualizado.` })
  } catch (err) {
    $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao atualizar perfil.' })
  }
}

onMounted(loadUsers)
</script>
