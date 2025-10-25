import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'

const mockDialogResult = {
  dialogRef: { value: null },
  onDialogHide: vi.fn(),
  onDialogOK: vi.fn(),
  onDialogCancel: vi.fn(),
}

vi.mock('quasar', async () => {
  const actual = await vi.importActual('quasar')
  const mockFn = () => mockDialogResult
  mockFn.emits = ['ok', 'hide', 'cancel']
  return {
    ...actual,
    useDialogPluginComponent: mockFn,
  }
})

import AppConfirmDialog from 'src/components/AppConfirmDialog.vue'

describe('AppConfirmDialog', () => {
  it('exibe a mensagem passada via prop', () => {
    const wrapper = mount(AppConfirmDialog, {
      props: { message: 'Deseja excluir este item?' },
      global: {
        stubs: {
          'q-dialog': { template: '<div><slot /></div>' },
          'q-card': { template: '<div><slot /></div>' },
          'q-card-section': { template: '<div><slot /></div>' },
          'q-card-actions': { template: '<div><slot /></div>' },
          'q-avatar': true,
          'q-btn': { template: '<button @click="$emit(\'click\')"><slot /></button>' },
        },
      },
    })

    expect(wrapper.text()).toContain('Deseja excluir este item?')
  })

  it('usa mensagem padrão quando prop não é fornecida', () => {
    const wrapper = mount(AppConfirmDialog, {
      global: {
        stubs: {
          'q-dialog': { template: '<div><slot /></div>' },
          'q-card': { template: '<div><slot /></div>' },
          'q-card-section': { template: '<div><slot /></div>' },
          'q-card-actions': { template: '<div><slot /></div>' },
          'q-avatar': true,
          'q-btn': { template: '<button @click="$emit(\'click\')"><slot /></button>' },
        },
      },
    })

    expect(wrapper.text()).toContain('Deseja confirmar esta ação?')
  })
})
