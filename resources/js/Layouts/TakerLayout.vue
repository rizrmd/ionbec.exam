<template>
  <div>
    <InertiaHead :title="title"/>
    <TransitionRoot :show="sidebarOpen" as="template">
      <Dialog as="div" class="fixed inset-0 flex z-40 md:hidden" @close="closeSidebar">
        <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0"
                         enter-to="opacity-100" leave="transition-opacity ease-linear duration-300"
                         leave-from="opacity-100" leave-to="opacity-0">
          <DialogOverlay class="fixed inset-0 bg-gray-600 bg-opacity-75"/>
        </TransitionChild>
        <TransitionChild as="template" enter="transition ease-in-out duration-300 transform"
                         enter-from="-translate-x-full" enter-to="translate-x-0"
                         leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0"
                         leave-to="-translate-x-full">
          <div class="relative flex-1 flex flex-col max-w-xs w-full pt-5 pb-4 bg-white">
            <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0"
                             enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100"
                             leave-to="opacity-0">
              <div class="absolute top-0 right-0 -mr-12 pt-2">
                <button class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                        type="button"
                        @click="closeSidebar">
                  <span class="sr-only">Close sidebar</span>
                  <XIcon aria-hidden="true" class="h-6 w-6 text-white"/>
                </button>
              </div>
            </TransitionChild>
            <div class="flex-shrink-0 flex items-center px-4">
              <img :src="logo" alt="IONBEC" class="h-8 w-auto">
              <h2 class="ml-1">IONBEC</h2>
            </div>
            <div class="mt-5 flex-1 h-0 overflow-y-auto">
              <nav class="px-2 space-y-1">
                <a v-for="item in sidebarNavigation" :key="item.name" :class="[item.current ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center px-2 py-2 text-base font-medium rounded-md']"
                   :href="item.href">
                  <component :is="item.icon"
                             :class="[item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-4 flex-shrink-0 h-6 w-6']"
                             aria-hidden="true"/>
                  {{ item.name }}
                </a>
              </nav>
            </div>
          </div>
        </TransitionChild>
        <div aria-hidden="true" class="flex-shrink-0 w-14">
          <!-- Dummy element to force sidebar to shrink to fit close icon -->
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Static sidebar for desktop -->
    <div class="hidden md:flex md:w-64 md:flex-col md:fixed md:inset-y-0">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div class="flex flex-col flex-grow border-r border-gray-200 pt-5 bg-white overflow-y-auto">
        <div class="flex items-center justify-center flex-shrink-0 px-4">
          <img alt="IONBEC" class="h-8 w-auto" src="/images/logo.png">
          <h2 class="ml-1">IONBEC</h2>
        </div>
        <div class="mt-5 flex-grow flex flex-col">
          <nav class="flex-1 px-2 pb-4 space-y-1">
            <div v-for="(item, index) in sidebarNavigation" :key="`sidebar-${index}`">
              <small v-if="item.hasOwnProperty('descriptionAbove')"
                     class="text-gray-600 font-bold font-medium px-2">{{ item.descriptionAbove }}</small>
              <Link :class="[item.current ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center px-2 py-2 text-sm font-medium rounded-md']"
                    :href="item.href">
                <component :is="item.icon"
                           :class="[item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-3 flex-shrink-0 h-6 w-6']"
                           aria-hidden="true"/>
                {{ item.name }}
              </Link>
            </div>
          </nav>
        </div>
      </div>
    </div>
    <div class="md:pl-64 flex flex-col flex-1">
      <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white shadow">
        <button class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 md:hidden"
                type="button"
                @click="sidebarOpen = true">
          <span class="sr-only">Open sidebar</span>
          <MenuAlt2Icon aria-hidden="true" class="h-6 w-6"/>
        </button>
        <div class="flex-1 px-4 flex justify-between">
          <div class="flex-1 flex">
            <!--            <form class="w-full flex md:ml-0" action="#" method="GET">-->
            <!--              <label for="search-field" class="sr-only">Search</label>-->
            <!--              <div class="relative w-full text-gray-400 focus-within:text-gray-600">-->
            <!--                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">-->
            <!--                  <SearchIcon class="h-5 w-5" aria-hidden="true" />-->
            <!--                </div>-->
            <!--                <input id="search-field" class="block w-full h-full pl-8 pr-3 py-2 border-transparent text-gray-900 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-0 focus:border-transparent sm:text-sm" placeholder="Search" type="search" name="search" />-->
            <!--              </div>-->
            <!--            </form>-->
          </div>
          <div class="ml-4 flex items-center md:ml-6">
            <button class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    type="button">
              <span class="sr-only">View notifications</span>
              <BellIcon aria-hidden="true" class="h-6 w-6"/>
            </button>

            <!-- Profile dropdown -->
            <Menu as="div" class="ml-3 relative">
              <div>
                <MenuButton
                  class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                  <span class="sr-only">Open user menu</span>
                  <img alt=""
                       class="h-8 w-8 rounded-full"
                       :src="getImage($page.props.auth.taker.name)"/>
                </MenuButton>
              </div>
              <transition enter-active-class="transition ease-out duration-100"
                          enter-from-class="transform opacity-0 scale-95"
                          enter-to-class="transform opacity-100 scale-100"
                          leave-active-class="transition ease-in duration-75"
                          leave-from-class="transform opacity-100 scale-100"
                          leave-to-class="transform opacity-0 scale-95">
                <MenuItems
                  class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                  <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                    <Link :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']" :href="item.href"
                          @click="e => item.onClick ? e.preventDefault() || item.onClick() : e">{{
                        item.name
                      }}
                    </Link>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>
      </div>

      <main class="flex-1">
        <div class="py-6 container">
          <div class="w-full px-1 sm:px-6 md:px-8 pb-6">
            <slot name="header">
              <h1 class="text-2xl font-semibold text-gray-900">{{ title }}</h1>
            </slot>

            <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4 mt-4" v-if="infoCard">
              <card-counter title="Upcoming Schedules" :icon="CalendarIcon" :count="upcomingDelivery"/>
              <card-counter title="Finished" :icon="BadgeCheckIcon" :count="finishedDelivery"/>
              <card-counter title="Avg. Scores" :icon="ChartBarIcon" :count="avgScore"/>
              <card-counter title="Last Signed-in" :icon="UserIcon" :count="lastSign"/>
            </div>
          </div>
          <div class="w-full px-4 sm:px-6 md:px-8 pt-2">
            <slot/>
          </div>
        </div>
      </main>
    </div>
  </div>
  <Notification/>
</template>

<script setup>
import {ref, toRefs} from 'vue'
import {
  Dialog,
  DialogOverlay,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import {BellIcon, MenuAlt2Icon, XIcon, CalendarIcon, BadgeCheckIcon, ChartBarIcon, UserIcon} from '@heroicons/vue/outline'
import {Head as InertiaHead, Link} from '@inertiajs/inertia-vue3'
import useNavigationStore from '@/Store/navigation-taker'
import {storeToRefs} from 'pinia'
import logo from '@images/logo.png'
import CardCounter from '@/Components/CardCounter'
import Notification from "@/Components/Notification";
import moment from  'moment'

const props = defineProps({
  title: {
    type: String,
    required: false,
    default: '',
  },
  infoCard: {
    type: Boolean,
    default: true,
  }
})

const navigationStore = useNavigationStore()

const {sidebarNavigation, userNavigation} = storeToRefs(navigationStore)

const {title} = toRefs(props)

const sidebarOpen = ref(false)
const closeSidebar = () => sidebarOpen.value = false

const getImage = (name, avatar = null) => {
  let image = 'https://ui-avatars.com/api/?background=4f46e5&color=fff&&name=' + encodeURIComponent(name);
  if (avatar) image = window.location.origin + '/storage/profile/' + avatar;

  return image;
}


const upcomingDelivery = ref(0);
const finishedDelivery = ref(0);
const avgScore = ref(0);
const lastSign = ref('-');
axios.get(route('taker.dashboard.card-data'))
  .then((res) => {
    const data = res.data
    upcomingDelivery.value = data.upcomingDelivery
    finishedDelivery.value = data.finishedDelivery
    avgScore.value = data.averageScore
    if (data.lastAttempt !== null) {
      lastSign.value = moment(data.lastAttempt).format('DD/MMM/YY  HH:mm')
    }
  })
  .catch((e) => console.log(e))
</script>
