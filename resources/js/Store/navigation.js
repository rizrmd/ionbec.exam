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
  ClipboardListIcon,
  OfficeBuildingIcon
} from "@heroicons/vue/outline"
import {defineStore} from 'pinia'
import route from "@/Libs/ziggy"
import {Inertia} from "@inertiajs/inertia"
import {reactive} from "vue"

const getDefaultSidebarNavigation = () => [
  {
    name: 'Dashboard',
    href: route('back-office.root-dashboard'),
    routeGroup: 'back-office.root-dashboard',
    icon: HomeIcon,
    current: false,
    allowed: ['root'],
  },
  {
    name: 'Client Management',
    href: route('back-office.clients.index'),
    routeGroup: 'back-office.clients',
    icon: OfficeBuildingIcon,
    current: false,
    allowed: ['root'],
  },
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
    allowed: ['administrator', 'scorer'],
  },
  {
    name: 'Results',
    href: route('back-office.result.index'),
    routeGroup: 'back-office.result',
    icon: ChartPieIcon,
    current: false,
    allowed: ['administrator', 'scorer'],
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
    name: 'User Management',
    href: route('back-office.users.index'),
    icon: UserGroupIcon,
    routeGroup: 'back-office.users',
    current: false,
    allowed: ['administrator'],
  },
  {
    name: 'Users & Access',
    href: route('back-office.user.index'),
    icon: LockOpenIcon,
    routeGroup: 'back-office.user',
    current: false,
    allowed: ['administrator'],
  },
  {
    name: 'Exam Logs',
    href: '/admin/exam-logs',
    icon: ClipboardListIcon,
    routeGroup: 'admin.exam-logs',
    current: false,
    allowed: ['administrator', 'root'],
  },
]

const getDefaultUserNavigation = () => [
  {name: 'Your Profile', href: route('back-office.profile.index')},
  // {name: 'Settings', href: '#'},
  {name: 'Sign out', href: '#', onClick: () => Inertia.post(route('logout'))},
]

export default defineStore('navigation', () => {
  const userNavigation = reactive([...getDefaultUserNavigation()])
  const sidebarNavigation = reactive([...getDefaultSidebarNavigation()])

  const refreshCurrentActivePage = () => {
    // Get current route name from URL instead of calling route() without parameters
    const currentPath = window.location.pathname;
    let currentRouteName = null;
    
    // Find matching route by comparing URI patterns
    if (window.Ziggy && window.Ziggy.routes) {
      for (const [routeName, routeData] of Object.entries(window.Ziggy.routes)) {
        const routeUri = routeData.uri.split('{')[0]; // Remove parameter parts
        if (currentPath.includes(routeUri) || routeUri.includes(currentPath)) {
          currentRouteName = routeName;
          break;
        }
      }
    }
    
    if (!currentRouteName) {
      return;
    }
    
    const currentRoute = currentRouteName.split('.');
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
      sidebarNavigation.forEach(item => item.current = (currentRouteName.includes(item?.routeGroup)))
    }
  }

  return {
    userNavigation,
    sidebarNavigation,
    refreshCurrentActivePage,
  }
})