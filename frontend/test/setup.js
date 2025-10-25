import { createPinia, setActivePinia } from 'pinia'
import { config } from '@vue/test-utils'
import { beforeEach } from 'vitest'

beforeEach(() => {
  setActivePinia(createPinia())
})

config.global.stubs = {
  'router-link': true,
  'router-view': true,
}
