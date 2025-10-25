<template>
  <q-page padding>
    <div class="text-h5 text-weight-bold q-mb-lg" style="letter-spacing: -0.3px">Dashboard</div>

    <div class="row q-col-gutter-md q-mb-lg">
      <div v-for="card in cards" :key="card.label" class="col-12 col-sm-6 col-md-3">
        <q-card flat bordered class="stat-card">
          <div class="stat-card-accent" :style="{ background: card.accentColor }" />
          <q-card-section class="row items-center no-wrap q-pa-md">
            <div class="stat-icon-wrap q-mr-md" :style="{ background: card.iconBg }">
              <q-icon :name="card.icon" size="22px" :color="card.iconColor" />
            </div>
            <div class="col">
              <div class="text-caption text-grey-6 q-mb-xs" style="font-size: 11px; text-transform: uppercase; letter-spacing: .5px">
                {{ card.label }}
              </div>
              <div class="text-h4 text-weight-bold" :class="card.color">
                <q-skeleton v-if="loading" type="text" width="48px" />
                <template v-else>{{ card.value }}</template>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-card flat bordered>
      <q-card-section class="row items-center q-pb-none">
        <q-icon name="assignment" color="primary" size="20px" class="q-mr-sm" />
        <div class="text-subtitle1 text-weight-medium">Locações ativas</div>
        <q-space />
        <q-badge v-if="!loading" color="primary" :label="activeRentals.length" />
      </q-card-section>
      <q-card-section>
        <q-table
          :rows="activeRentals"
          :columns="columns"
          row-key="id"
          :loading="loading"
          flat
          no-data-label="Nenhuma locação ativa no momento"
          :rows-per-page-options="[10, 20, 0]"
        >
          <template #body-cell-client="props">
            <q-td :props="props">
              <div class="row items-center no-wrap">
                <q-icon name="person" size="16px" color="grey-5" class="q-mr-xs" />
                {{ props.row.client?.name }}
              </div>
            </q-td>
          </template>
          <template #body-cell-car="props">
            <q-td :props="props">
              <q-chip dense color="blue-1" text-color="primary" icon="directions_car" size="sm">
                {{ props.row.car?.plate }}
              </q-chip>
            </q-td>
          </template>
          <template #body-cell-start="props">
            <q-td :props="props">{{ formatDateBr(props.row.period_start_date) }}</q-td>
          </template>
          <template #body-cell-expected_end="props">
            <q-td :props="props">
              <span :class="isLate(props.row) ? 'text-negative text-weight-medium' : ''">
                {{ formatDateBr(props.row.period_expected_end_date) }}
                <q-icon v-if="isLate(props.row)" name="warning" size="14px" color="negative" class="q-ml-xs">
                  <q-tooltip>Devolução em atraso</q-tooltip>
                </q-icon>
              </span>
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { rentalService } from 'src/services/rental.service'
import { carService } from 'src/services/car.service'
import { clientService } from 'src/services/client.service'
import { formatDateBr } from 'src/utils/formatDate'

const loading = ref(false)
const rentals = ref([])
const totalCars = ref(0)
const totalClients = ref(0)

const activeRentals = computed(() => rentals.value.filter(r => !r.period_actual_end_date))

function isLate(row) {
  if (!row.period_expected_end_date) return false
  const iso = row.period_expected_end_date.replace(' ', 'T')
  return new Date(iso) < new Date()
}

const cards = computed(() => [
  {
    label: 'Locações ativas',
    value: activeRentals.value.length,
    icon: 'assignment',
    color: 'text-primary',
    iconColor: 'primary',
    iconBg: '#e3f2fd',
    accentColor: '#1565c0',
  },
  {
    label: 'Total de veículos',
    value: totalCars.value,
    icon: 'directions_car',
    color: 'text-secondary',
    iconColor: 'secondary',
    iconBg: '#fce4ec',
    accentColor: '#c2185b',
  },
  {
    label: 'Total de clientes',
    value: totalClients.value,
    icon: 'people',
    color: 'text-positive',
    iconColor: 'positive',
    iconBg: '#e8f5e9',
    accentColor: '#388e3c',
  },
  {
    label: 'Locações concluídas',
    value: rentals.value.filter(r => r.period_actual_end_date).length,
    icon: 'check_circle',
    color: 'text-grey-7',
    iconColor: 'grey-6',
    iconBg: '#f5f5f5',
    accentColor: '#9e9e9e',
  },
])

const columns = [
  { name: 'client', label: 'Cliente', field: 'client', align: 'left' },
  { name: 'car', label: 'Veículo', field: 'car', align: 'left' },
  { name: 'start', label: 'Início', field: 'period_start_date', align: 'left' },
  { name: 'expected_end', label: 'Prev. devolução', field: 'period_expected_end_date', align: 'left' },
]

onMounted(async () => {
  loading.value = true
  try {
    const [r, c, cl] = await Promise.all([
      rentalService.list(),
      carService.list(),
      clientService.list(),
    ])
    rentals.value = r.data ?? r
    totalCars.value = c.meta?.total ?? (c.data ?? c).length
    totalClients.value = cl.meta?.total ?? (cl.data ?? cl).length
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.stat-card {
  position: relative;
  overflow: hidden;
  border-radius: 12px;
}

.stat-card-accent {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  border-radius: 12px 0 0 12px;
}

.stat-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
</style>
