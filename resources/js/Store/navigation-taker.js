import {
  CalendarIcon,
  HomeIcon,
  UserIcon,
} from "@heroicons/vue/outline"
import {defineStore} from 'pinia'
import route from "@/Libs/ziggy"
import {Inertia} from "@inertiajs/inertia"
import {reactive} from "vue"

const defaultSidebarNavigation = [
  {
    name: 'Dashboard',
    href: route('taker.dashboard'),
    routeGroup: 'taker.dashboard',
    icon: HomeIcon,
    current: false,
  },
  {
    name: 'Schedules',
    href: route('taker.schedules.index'),
    routeGroup: 'taker.schedules',
    icon: CalendarIcon,
    current: false,
  },
  {
    name: 'My Profile',
    href: route('taker.profile.index'),
    routeGroup: 'taker.profile',
    icon: UserIcon,
    current: false,
    descriptionAbove: 'Settings'
  },
]

const defaultUserNavigation = [
  {name: 'Your Profile', href: route('taker.profile.index')},
  // {name: 'Settings', href: '#'},
  {name: 'Sign out', href: '#', onClick: () => Inertia.post(route('logout'))},
]

export default defineStore('navigation-taker', () => {
  const userNavigation = reactive([...defaultUserNavigation])
  const sidebarNavigation = reactive([...defaultSidebarNavigation])

  const refreshCurrentActivePage = () => {
    const currentRoute = route().current().split('.');
    if (currentRoute.length >= 2) {
      sidebarNavigation.forEach(item => {
        if (item.routeGroup !== undefined) {
          const routeGroupSplit = item.routeGroup.split('.');
          if (routeGroupSplit.length >= 2) {
            item.current = routeGroupSplit[1] === currentRoute[1]
          }
        }
      })
    } else {
      sidebarNavigation.forEach(item => item.current = (route().current().includes(item?.routeGroup)))
    }
  }

  return {
    userNavigation,
    sidebarNavigation,
    refreshCurrentActivePage,
  }
})
