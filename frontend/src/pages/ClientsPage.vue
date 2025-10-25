<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <q-icon name="people" size="28px" color="primary" class="q-mr-sm" />
      <div class="text-h5 text-weight-bold">Clientes</div>
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
      <q-btn icon="add" label="Novo cliente" color="primary" unelevated @click="openForm()" />
    </div>

    <q-table
      :rows="clients"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :filter="search"
      flat
      bordered
      no-data-label="Nenhum cliente cadastrado"
      :rows-per-page-options="[10, 20, 0]"
    >
      <template #body-cell-actions="props">
        <q-td :props="props">
          <q-btn flat round dense icon="edit" size="sm" color="grey-7" @click="openForm(props.row)">
            <q-tooltip>Editar</q-tooltip>
          </q-btn>
          <q-btn flat round dense icon="delete" size="sm" color="negative" @click="handleDelete(props.row)">
            <q-tooltip>Excluir</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="dialog">
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editing ? 'Editar cliente' : 'Novo cliente' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit.prevent="save">
            <q-input v-model="form.name" label="Nome" outlined :rules="[val => !!val || 'Informe o nome']" class="q-mb-sm" />
            <q-input
              v-model="form.cpf"
              label="CPF"
              outlined
              mask="###.###.###-##"
              :rules="[val => !!val || 'Informe o CPF']"
              class="q-mb-sm"
            />
            <q-input v-model="form.email" label="E-mail" type="email" outlined class="q-mb-sm" />
            <q-input
              v-model="form.phone"
              label="Telefone"
              outlined
              mask="(##) #####-####"
              :rules="[val => !val || val.length === 15 || 'Telefone inválido']"
              class="q-mb-md"
            />
            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn type="submit" label="Salvar" color="primary" :loading="saving" />
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
import { clientService } from 'src/services/client.service'
import AppConfirmDialog from 'src/components/AppConfirmDialog.vue'

const $q = useQuasar()
const clients = ref([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const editing = ref(null)
const search = ref('')
const form = ref({ name: '', cpf: '', email: '', phone: '' })

const columns = [
  { name: 'name', label: 'Nome', field: 'name', align: 'left', sortable: true },
  { name: 'cpf', label: 'CPF', field: 'cpf', align: 'left' },
  { name: 'email', label: 'E-mail', field: 'email', align: 'left' },
  { name: 'phone', label: 'Telefone', field: 'phone', align: 'left' },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'right' },
]

async function loadClients() {
  loading.value = true
  try {
    const res = await clientService.list()
    clients.value = res.data ?? res
  } finally {
    loading.value = false
  }
}

function openForm(client = null) {
  editing.value = client
  form.value = {
    name: client?.name ?? '',
    cpf: client?.cpf ?? '',
    email: client?.email ?? '',
    phone: client?.phone ?? '',
  }
  dialog.value = true
}

async function save() {
  saving.value = true
  try {
    if (editing.value) {
      await clientService.update(editing.value.id, form.value)
      $q.notify({ type: 'positive', message: 'Cliente atualizado.' })
    } else {
      await clientService.create(form.value)
      $q.notify({ type: 'positive', message: 'Cliente criado.' })
    }
    dialog.value = false
    await loadClients()
  } catch (err) {
    $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao salvar.' })
  } finally {
    saving.value = false
  }
}

function handleDelete(client) {
  $q.dialog({ component: AppConfirmDialog, componentProps: { message: `Excluir o cliente "${client.name}"?` } })
    .onOk(async () => {
      try {
        await clientService.remove(client.id)
        $q.notify({ type: 'positive', message: 'Cliente excluído.' })
        await loadClients()
      } catch (err) {
        $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao excluir.' })
      }
    })
}

onMounted(loadClients)
</script>
