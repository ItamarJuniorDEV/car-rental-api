<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <q-icon name="assignment" size="28px" color="primary" class="q-mr-sm" />
      <div class="text-h5 text-weight-bold">Locações</div>
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
      <q-btn icon="add" label="Nova locação" color="primary" unelevated @click="openCreateDialog()" />
    </div>

    <q-table
      :rows="rentals"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :filter="search"
      flat
      bordered
      no-data-label="Nenhuma locação cadastrada"
      :rows-per-page-options="[10, 20, 0]"
      :row-class="rowClass"
    >
      <template #body-cell-client="props">
        <q-td :props="props">{{ props.row.client?.name }}</q-td>
      </template>
      <template #body-cell-car="props">
        <q-td :props="props">{{ props.row.car?.plate }}</q-td>
      </template>
      <template #body-cell-period_start_date="props">
        <q-td :props="props">{{ formatDateBr(props.row.period_start_date) }}</q-td>
      </template>
      <template #body-cell-period_expected_end_date="props">
        <q-td :props="props">{{ formatDateBr(props.row.period_expected_end_date) }}</q-td>
      </template>
      <template #body-cell-period_actual_end_date="props">
        <q-td :props="props">
          <span v-if="props.row.period_actual_end_date">{{ formatDateBr(props.row.period_actual_end_date) }}</span>
          <span v-else class="text-grey-6">Em andamento</span>
        </q-td>
      </template>
      <template #body-cell-total="props">
        <q-td :props="props">{{ formatBrl(props.row.total) }}</q-td>
      </template>
      <template #body-cell-actions="props">
        <q-td :props="props">
          <q-btn
            v-if="!props.row.period_actual_end_date"
            flat
            round
            dense
            icon="assignment_return"
            size="sm"
            color="primary"
            @click="openReturnDialog(props.row)"
          >
            <q-tooltip>Registrar devolução</q-tooltip>
          </q-btn>
          <q-btn flat round dense icon="delete" size="sm" color="negative" @click="handleDelete(props.row)">
            <q-tooltip>Excluir</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>

    <q-dialog v-model="createDialog">
      <q-card style="min-width: 440px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Nova locação</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form @submit.prevent="createRental">
            <q-select
              v-model="createForm.client_id"
              :options="clientOptions"
              option-value="id"
              option-label="name"
              emit-value
              map-options
              label="Cliente"
              outlined
              :rules="[val => !!val || 'Selecione o cliente']"
              class="q-mb-sm"
            />
            <q-select
              v-model="createForm.car_id"
              :options="availableCars"
              option-value="id"
              option-label="label"
              emit-value
              map-options
              label="Veículo disponível"
              outlined
              :rules="[val => !!val || 'Selecione o veículo']"
              class="q-mb-sm"
            />
            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-input
                  v-model="createForm.period_start_date"
                  label="Data de início"
                  outlined
                  type="date"
                  :rules="[val => !!val || 'Informe a data']"
                  class="q-mb-sm"
                />
              </div>
              <div class="col-6">
                <q-input
                  v-model="createForm.period_expected_end_date"
                  label="Prev. devolução"
                  outlined
                  type="date"
                  :rules="[val => !!val || 'Informe a data']"
                  class="q-mb-sm"
                />
              </div>
            </div>
            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-input
                  v-model.number="createForm.initial_km"
                  label="KM inicial"
                  type="number"
                  outlined
                  min="0"
                  :rules="[val => val !== null && val !== '' || 'Informe o KM']"
                  class="q-mb-md"
                />
              </div>
              <div class="col-6">
                <q-input
                  v-model.number="createForm.daily_rate"
                  label="Diária (R$)"
                  type="number"
                  outlined
                  min="1"
                  prefix="R$"
                  :rules="[val => !!val || 'Informe a diária']"
                  class="q-mb-md"
                />
              </div>
            </div>
            <div v-if="createError" class="text-negative q-mb-sm text-caption">{{ createError }}</div>
            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn type="submit" label="Criar locação" color="primary" :loading="saving" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="returnDialog">
      <q-card style="min-width: 420px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Registrar devolução</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section class="q-pt-none">
          <div class="text-caption text-grey">
            Cliente: {{ returningRental?.client?.name }} — Veículo: {{ returningRental?.car?.plate }}
          </div>
        </q-card-section>

        <q-card-section>
          <q-form @submit.prevent="doReturnRental">
            <q-input
              v-model="returnForm.period_actual_end_date"
              label="Data de devolução"
              outlined
              type="date"
              :rules="[val => !!val || 'Informe a data']"
              class="q-mb-sm"
            />
            <q-input
              v-model.number="returnForm.final_km"
              label="KM final"
              type="number"
              outlined
              min="0"
              :rules="[val => val !== null && val !== '' || 'Informe o KM final']"
              class="q-mb-md"
            />
            <div v-if="returnError" class="text-negative q-mb-sm text-caption">{{ returnError }}</div>
            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn type="submit" label="Confirmar devolução" color="primary" :loading="saving" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="returnSummaryDialog">
      <q-card style="min-width: 340px">
        <q-card-section class="text-center">
          <q-icon name="check_circle" color="positive" size="48px" />
          <div class="text-h6 q-mt-sm">Devolução registrada</div>
        </q-card-section>
        <q-card-section>
          <div class="row q-gutter-md justify-center">
            <div class="text-center">
              <div class="text-caption text-grey">Total</div>
              <div class="text-h5 text-primary">{{ formatBrl(returnResult?.total) }}</div>
            </div>
            <div v-if="returnResult?.late_fee" class="text-center">
              <div class="text-caption text-grey">Multa por atraso</div>
              <div class="text-h5 text-negative">{{ formatBrl(returnResult?.late_fee) }}</div>
            </div>
          </div>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Fechar" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import { rentalService } from 'src/services/rental.service'
import { carService } from 'src/services/car.service'
import { clientService } from 'src/services/client.service'
import AppConfirmDialog from 'src/components/AppConfirmDialog.vue'
import { formatDateBr } from 'src/utils/formatDate'
import { formatBrl } from 'src/utils/formatCurrency'

const $q = useQuasar()
const rentals = ref([])
const clientOptions = ref([])
const availableCars = ref([])
const loading = ref(false)
const saving = ref(false)
const search = ref('')

const createDialog = ref(false)
const createError = ref('')
const createForm = ref({
  client_id: null,
  car_id: null,
  period_start_date: '',
  period_expected_end_date: '',
  initial_km: 0,
  daily_rate: null,
})

const returnDialog = ref(false)
const returnSummaryDialog = ref(false)
const returnError = ref('')
const returningRental = ref(null)
const returnForm = ref({ period_actual_end_date: '', final_km: 0 })
const returnResult = ref(null)

const columns = [
  { name: 'client', label: 'Cliente', field: 'client', align: 'left' },
  { name: 'car', label: 'Veículo', field: 'car', align: 'left' },
  { name: 'period_start_date', label: 'Início', field: 'period_start_date', align: 'left' },
  { name: 'period_expected_end_date', label: 'Prev. devolução', field: 'period_expected_end_date', align: 'left' },
  { name: 'period_actual_end_date', label: 'Devolvido em', field: 'period_actual_end_date', align: 'left' },
  { name: 'total', label: 'Total', field: 'total', align: 'right' },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'right' },
]

function rowClass(row) {
  return !row.period_actual_end_date ? 'active-rental-row' : ''
}

async function loadRentals() {
  loading.value = true
  try {
    const res = await rentalService.list()
    rentals.value = res.data ?? res
  } finally {
    loading.value = false
  }
}

async function openCreateDialog() {
  createError.value = ''
  createForm.value = {
    client_id: null,
    car_id: null,
    period_start_date: '',
    period_expected_end_date: '',
    initial_km: 0,
    daily_rate: null,
  }

  try {
    const [cRes, carRes] = await Promise.all([clientService.list(), carService.list()])
    clientOptions.value = cRes.data ?? cRes
    const allCars = carRes.data ?? carRes
    availableCars.value = allCars
      .filter(c => c.available)
      .map(c => ({ ...c, label: `${c.plate} — ${c.line?.brand?.name} ${c.line?.name}` }))
    createDialog.value = true
  } catch {
    $q.notify({ type: 'negative', message: 'Erro ao carregar dados. Tente novamente.' })
  }
}

async function createRental() {
  saving.value = true
  createError.value = ''
  try {
    await rentalService.create({
      client_id: createForm.value.client_id,
      car_id: createForm.value.car_id,
      period_start_date: createForm.value.period_start_date,
      period_expected_end_date: createForm.value.period_expected_end_date,
      initial_km: Number(createForm.value.initial_km),
      daily_rate: Number(createForm.value.daily_rate),
    })
    $q.notify({ type: 'positive', message: 'Locação criada com sucesso.' })
    createDialog.value = false
    await loadRentals()
  } catch (err) {
    createError.value = err.response?.data?.erro || err.response?.data?.message || 'Erro ao criar locação.'
  } finally {
    saving.value = false
  }
}

function openReturnDialog(rental) {
  returningRental.value = rental
  returnForm.value = { period_actual_end_date: '', final_km: 0 }
  returnResult.value = null
  returnError.value = ''
  returnDialog.value = true
}

async function doReturnRental() {
  saving.value = true
  returnError.value = ''
  try {
    const res = await rentalService.returnRental(returningRental.value.id, {
      period_actual_end_date: returnForm.value.period_actual_end_date,
      final_km: Number(returnForm.value.final_km),
    })
    returnResult.value = res.data ?? res
    returnDialog.value = false
    returnSummaryDialog.value = true
    await loadRentals()
  } catch (err) {
    returnError.value = err.response?.data?.erro || err.response?.data?.message || 'Erro ao registrar devolução.'
  } finally {
    saving.value = false
  }
}

function handleDelete(rental) {
  $q.dialog({
    component: AppConfirmDialog,
    componentProps: { message: 'Excluir esta locação? Esta ação não pode ser desfeita.' },
  }).onOk(async () => {
    try {
      await rentalService.remove(rental.id)
      $q.notify({ type: 'positive', message: 'Locação excluída.' })
      await loadRentals()
    } catch (err) {
      $q.notify({ type: 'negative', message: err.response?.data?.erro || err.response?.data?.message || 'Erro ao excluir.' })
    }
  })
}

onMounted(loadRentals)
</script>

<style>
.active-rental-row {
  background: #f1f8e9 !important;
}
</style>
