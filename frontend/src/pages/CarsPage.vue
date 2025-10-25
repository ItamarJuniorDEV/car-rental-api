<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <q-icon name="directions_car" size="28px" color="primary" class="q-mr-sm" />
      <div class="text-h5 text-weight-bold">Veículos</div>
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
      <q-btn icon="add" label="Novo veículo" color="primary" unelevated @click="openForm()" />
    </div>

    <q-table
      :rows="cars"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :filter="search"
      flat
      bordered
      no-data-label="Nenhum veículo cadastrado"
      :rows-per-page-options="[10, 20, 0]"
    >
      <template #body-cell-line="props">
        <q-td :props="props">{{ props.row.line?.brand?.name }} / {{ props.row.line?.name }}</q-td>
      </template>
      <template #body-cell-km="props">
        <q-td :props="props">{{ props.row.km?.toLocaleString('pt-BR') }} km</q-td>
      </template>
      <template #body-cell-available="props">
        <q-td :props="props">
          <q-badge :color="props.row.available ? 'positive' : 'orange'">
            {{ props.row.available ? 'Disponível' : 'Locado' }}
          </q-badge>
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
      <q-card style="min-width: 400px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editing ? 'Editar veículo' : 'Novo veículo' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit.prevent="save">
            <q-select
              v-model="form.line_id"
              :options="lineOptions"
              option-value="id"
              option-label="label"
              emit-value
              map-options
              label="Linha"
              outlined
              :rules="[val => !!val || 'Selecione a linha']"
              class="q-mb-sm"
            />
            <q-input
              v-model="form.plate"
              label="Placa"
              outlined
              :rules="[val => !!val || 'Informe a placa']"
              class="q-mb-sm"
            />
            <q-input
              v-model.number="form.km"
              label="Quilometragem atual"
              type="number"
              outlined
              min="0"
              :rules="[val => val !== null && val !== '' || 'Informe o KM']"
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
import { carService } from 'src/services/car.service'
import { lineService } from 'src/services/line.service'
import AppConfirmDialog from 'src/components/AppConfirmDialog.vue'

const $q = useQuasar()
const cars = ref([])
const lineOptions = ref([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const editing = ref(null)
const search = ref('')
const form = ref({ line_id: null, plate: '', km: 0 })

const columns = [
  { name: 'plate', label: 'Placa', field: 'plate', align: 'left', sortable: true },
  { name: 'line', label: 'Marca / Linha', field: 'line', align: 'left' },
  { name: 'km', label: 'KM', field: 'km', align: 'left' },
  { name: 'available', label: 'Status', field: 'available', align: 'left' },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'right' },
]

async function loadCars() {
  loading.value = true
  try {
    const [cRes, lRes] = await Promise.all([carService.list(), lineService.list()])
    cars.value = cRes.data ?? cRes
    const rawLines = lRes.data ?? lRes
    lineOptions.value = rawLines.map(l => ({ ...l, label: `${l.brand?.name} / ${l.name}` }))
  } finally {
    loading.value = false
  }
}

function openForm(car = null) {
  editing.value = car
  form.value = {
    line_id: car?.line_id ?? null,
    plate: car?.plate ?? '',
    km: car?.km ?? 0,
  }
  dialog.value = true
}

async function save() {
  saving.value = true
  try {
    const payload = { ...form.value }
    if (!editing.value) {
      payload.available = true
    }
    if (editing.value) {
      await carService.update(editing.value.id, payload)
      $q.notify({ type: 'positive', message: 'Veículo atualizado.' })
    } else {
      await carService.create(payload)
      $q.notify({ type: 'positive', message: 'Veículo criado.' })
    }
    dialog.value = false
    await loadCars()
  } catch (err) {
    $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao salvar.' })
  } finally {
    saving.value = false
  }
}

function handleDelete(car) {
  $q.dialog({ component: AppConfirmDialog, componentProps: { message: `Excluir o veículo "${car.plate}"?` } })
    .onOk(async () => {
      try {
        await carService.remove(car.id)
        $q.notify({ type: 'positive', message: 'Veículo excluído.' })
        await loadCars()
      } catch (err) {
        $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao excluir.' })
      }
    })
}

onMounted(loadCars)
</script>
