require('./bootstrap')

import {createApp, h} from 'vue'
import {createInertiaApp} from '@inertiajs/inertia-vue3'
import {InertiaProgress} from '@inertiajs/progress'
import {Inertia} from '@inertiajs/inertia'
import {Plugin as ZiggyPlugin} from './Libs/ziggy'
import {createPinia} from 'pinia'
import useNavigationStore from '@/Store/navigation'
import useNavigationStoreTaker from '@/Store/navigation-taker'


const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'IONBEC'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => require(`./Pages/${name}.vue`),
  setup({el, app, props, plugin}) {
    createApp({render: () => h(app, props)})
      .use(createPinia())
      .use(plugin)
      .use(ZiggyPlugin)
      .mount(el)

    const navigationStore = useNavigationStore()
    const navigationStoreTaker = useNavigationStoreTaker()

    Inertia.on('navigate', () => {
      navigationStore.refreshCurrentActivePage()
      navigationStoreTaker.refreshCurrentActivePage()
    })
  },
}).then(_ => _)

InertiaProgress.init({color: '#4B5563'})
