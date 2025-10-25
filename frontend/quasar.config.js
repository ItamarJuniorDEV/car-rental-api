import { configure } from 'quasar/wrappers'

export default configure(function () {
  return {
    boot: ['pinia', 'axios'],

    css: ['app.scss'],

    extras: ['material-icons'],

    build: {
      target: {
        browser: ['es2019', 'edge88', 'firefox78', 'chrome87', 'safari13.1'],
        node: 'node20',
      },

      vueRouterMode: 'history',

      env: {
        API_URL: process.env.API_URL || 'http://localhost:8001/api',
      },
    },

    devServer: {
      open: false,
    },

    framework: {
      lang: 'pt-BR',

      config: {
        notify: {
          position: 'top-right',
          timeout: 3000,
        },
      },

      plugins: ['Dialog', 'Notify'],
    },

    animations: [],
  }
})
