<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <q-icon name="category" size="28px" color="primary" class="q-mr-sm" />
      <div class="text-h5 text-weight-bold">Linhas</div>
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
      <q-btn icon="add" label="Nova linha" color="primary" unelevated @click="openForm()" />
    </div>

    <q-table
      :rows="lines"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :filter="search"
      flat
      bordered
      no-data-label="Nenhuma linha cadastrada"
      :rows-per-page-options="[10, 20, 0]"
    >
      <template #body-cell-brand="props">
        <q-td :props="props">{{ props.row.brand?.name }}</q-td>
      </template>
      <template #body-cell-specs="props">
        <q-td :props="props">
          <span v-if="props.row.seats">{{ props.row.seats }} lugares</span>
          <span v-if="props.row.door_count"> · {{ props.row.door_count }} portas</span>
          <q-badge v-if="props.row.air_bag" color="blue-2" text-color="blue-9" class="q-ml-xs">Airbag</q-badge>
          <q-badge v-if="props.row.abs" color="green-2" text-color="green-9" class="q-ml-xs">ABS</q-badge>
        </q-td>
      </template>
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
      <q-card style="min-width: 420px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editing ? 'Editar linha' : 'Nova linha' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit.prevent="save">
            <q-select
              v-model="form.brand_id"
              :options="brandOptions"
              option-value="id"
              option-label="name"
              emit-value
              map-options
              label="Marca"
              outlined
              :rules="[val => !!val || 'Selecione a marca']"
              class="q-mb-sm"
            />
            <q-input
              v-model="form.name"
              label="Nome"
              outlined
              :rules="[val => !!val || 'Informe o nome']"
              class="q-mb-sm"
            />
            <div class="row q-col-gutter-sm q-mb-sm">
              <div class="col-6">
                <q-input
                  v-model.number="form.seats"
                  label="Lugares"
                  type="number"
                  outlined
                  min="1"
                />
              </div>
              <div class="col-6">
                <q-input
                  v-model.number="form.door_count"
                  label="Portas"
                  type="number"
                  outlined
                  min="1"
                />
              </div>
            </div>
            <div class="row q-col-gutter-sm q-mb-md">
              <div class="col-6">
                <q-toggle v-model="form.air_bag" label="Airbag" />
              </div>
              <div class="col-6">
                <q-toggle v-model="form.abs" label="ABS" />
              </div>
            </div>
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
import { lineService } from 'src/services/line.service'
import { brandService } from 'src/services/brand.service'
import AppConfirmDialog from 'src/components/AppConfirmDialog.vue'

const $q = useQuasar()
const lines = ref([])
const brandOptions = ref([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const editing = ref(null)
const search = ref('')
const form = ref({ brand_id: null, name: '', seats: null, door_count: null, air_bag: false, abs: false })

const columns = [
  { name: 'name', label: 'Nome', field: 'name', align: 'left', sortable: true },
  { name: 'brand', label: 'Marca', field: 'brand', align: 'left', sortable: true },
  { name: 'specs', label: 'Especificações', field: 'specs', align: 'left' },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'right' },
]

async function loadLines() {
  loading.value = true
  try {
    const [lRes, bRes] = await Promise.all([lineService.list(), brandService.list()])
    lines.value = lRes.data ?? lRes
    brandOptions.value = bRes.data ?? bRes
  } finally {
    loading.value = false
  }
}

function openForm(line = null) {
  editing.value = line
  form.value = {
    brand_id: line?.brand_id ?? null,
    name: line?.name ?? '',
    seats: line?.seats ?? null,
    door_count: line?.door_count ?? null,
    air_bag: line?.air_bag ?? false,
    abs: line?.abs ?? false,
  }
  dialog.value = true
}

async function save() {
  saving.value = true
  try {
    if (editing.value) {
      await lineService.update(editing.value.id, form.value)
      $q.notify({ type: 'positive', message: 'Linha atualizada.' })
    } else {
      await lineService.create(form.value)
      $q.notify({ type: 'positive', message: 'Linha criada.' })
    }
    dialog.value = false
    await loadLines()
  } catch (err) {
    $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao salvar.' })
  } finally {
    saving.value = false
  }
}

function handleDelete(line) {
  $q.dialog({ component: AppConfirmDialog, componentProps: { message: `Excluir a linha "${line.name}"?` } })
    .onOk(async () => {
      try {
        await lineService.remove(line.id)
        $q.notify({ type: 'positive', message: 'Linha excluída.' })
        await loadLines()
      } catch (err) {
        $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao excluir.' })
      }
    })
}

onMounted(loadLines)
</script>
