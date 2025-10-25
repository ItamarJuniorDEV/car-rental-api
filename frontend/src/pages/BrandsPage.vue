<template>
  <q-page padding>
    <div class="row items-center q-mb-md">
      <q-icon name="local_offer" size="28px" color="primary" class="q-mr-sm" />
      <div class="text-h5 text-weight-bold">Marcas</div>
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
      <q-btn icon="add" label="Nova marca" color="primary" unelevated @click="openForm()" />
    </div>

    <q-table
      :rows="brands"
      :columns="columns"
      row-key="id"
      :loading="loading"
      :filter="search"
      flat
      bordered
      no-data-label="Nenhuma marca cadastrada"
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
      <q-card style="min-width: 360px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ editing ? 'Editar marca' : 'Nova marca' }}</div>
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
              class="q-mb-md"
            />
            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" v-close-popup />
              <q-btn type="submit" label="Salvar" color="primary" unelevated :loading="saving" />
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
import { brandService } from 'src/services/brand.service'
import AppConfirmDialog from 'src/components/AppConfirmDialog.vue'

const $q = useQuasar()
const brands = ref([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const editing = ref(null)
const search = ref('')
const form = ref({ name: '' })

const columns = [
  { name: 'name', label: 'Nome', field: 'name', align: 'left', sortable: true },
  { name: 'actions', label: 'Ações', field: 'actions', align: 'right' },
]

async function loadBrands() {
  loading.value = true
  try {
    const res = await brandService.list()
    brands.value = res.data ?? res
  } finally {
    loading.value = false
  }
}

function openForm(brand = null) {
  editing.value = brand
  form.value = { name: brand?.name ?? '' }
  dialog.value = true
}

async function save() {
  saving.value = true
  try {
    if (editing.value) {
      await brandService.update(editing.value.id, form.value)
      $q.notify({ type: 'positive', message: 'Marca atualizada.' })
    } else {
      await brandService.create(form.value)
      $q.notify({ type: 'positive', message: 'Marca criada.' })
    }
    dialog.value = false
    await loadBrands()
  } catch (err) {
    $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao salvar.' })
  } finally {
    saving.value = false
  }
}

function handleDelete(brand) {
  $q.dialog({ component: AppConfirmDialog, componentProps: { message: `Excluir a marca "${brand.name}"?` } })
    .onOk(async () => {
      try {
        await brandService.remove(brand.id)
        $q.notify({ type: 'positive', message: 'Marca excluída.' })
        await loadBrands()
      } catch (err) {
        $q.notify({ type: 'negative', message: err.response?.data?.erro || 'Erro ao excluir.' })
      }
    })
}

onMounted(loadBrands)
</script>
