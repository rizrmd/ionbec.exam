import {
  ChartBarIcon,
  ChartPieIcon,
  CalendarIcon,
  BadgeCheckIcon,
  LockOpenIcon,
  HomeIcon,
  TagIcon,
  UserCircleIcon,
  SearchCircleIcon,
  UserGroupIcon,
  UserIcon,
  ClipboardListIcon
} from "@heroicons/vue/outline"
import {defineStore} from 'pinia'
import route from "@/Libs/ziggy"
import {Inertia} from "@inertiajs/inertia"
import {reactive} from "vue"

const defaultSidebarNavigation = [
  {
    name: 'Dashboard',
    href: route('back-office.dashboard'),
    routeGroup: 'back-office.dashboard',
    icon: HomeIcon,
    current: false,
    allowed: ['administrator'],
  },
  {
    name: 'Deliveries',
    href: route('back-office.delivery.index'),
    routeGroup: 'back-office.delivery',
    icon: CalendarIcon,
    current: false,
    allowed: ['administrator'],
  },
  {
    name: 'Exams',
    href: route('back-office.test.index'),
    routeGroup: 'back-office.test',
    icon: ClipboardListIcon,
    current: false,
    allowed: ['administrator'],
  },
  {
    name: 'Groups',
    href: route('back-office.group.index'),
    routeGroup: 'back-office.group',
    icon: UserGroupIcon,
    current: false,
    allowed: ['administrator'],
  },
  {
    descriptionAbove: 'Manage',
    name: 'Categories',
    href: route('back-office.category.index'),
    routeGroup: 'back-office.category',
    icon: TagIcon,
    current: false,
    allowed: ['administrator'],
  },
  {
    name: 'Question Sets',
    href: route('back-office.question-set.index'),
    routeGroup: 'back-office.question-set',
    icon: BadgeCheckIcon,
    current: false,
    allowed: ['administrator'],
  },
  {
    name: 'Search Questions',
    href: route('back-office.question-pack.index'),
    routeGroup: 'back-office.question-pack',
    icon: SearchCircleIcon,
    current: false,
    allowed: ['administrator'],
  },
  {
    name: 'Candidates',
    href: route('back-office.test-taker.index'),
    routeGroup: 'back-office.test-taker',
    icon: UserCircleIcon,
    current: false,
    allowed: ['administrator'],
  },
  {
    descriptionAbove: 'Reports',
    name: 'Scoring',
    href: route('back-office.scoring.index'),
    routeGroup: 'back-office.scoring',
    icon: ChartBarIcon,
    current: false,
  },
  {
    name: 'Results',
    href: route('back-office.result.index'),
    routeGroup: 'back-office.result',
    icon: ChartPieIcon,
    current: false,
  },
  {
    descriptionAbove: 'Settings',
    name: 'My Profile',
    href: route('back-office.profile.index'),
    routeGroup: 'back-office.profile',
    icon: UserIcon,
    current: false,
  },
  {
    name: 'Users & Access',
    href: route('back-office.user.index'),
    icon: LockOpenIcon,
    routeGroup: 'back-office.user',
    current: false,
    allowed: ['administrator'],
  },
]

const defaultUserNavigation = [
  {name: 'Your Profile', href: route('back-office.profile.index')},
  // {name: 'Settings', href: '#'},
  {name: 'Sign out', href: '#', onClick: () => Inertia.post(route('logout'))},
]

export default defineStore('navigation', () => {
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
