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
              <img :src="clientLogo" :alt="clientName" class="h-8 w-auto">
              <h2 class="ml-1">{{ clientName }}</h2>
            </div>
            <!-- Scrollable navigation section -->
            <div class="mt-5 flex-1 overflow-y-auto">
              <nav class="px-2 space-y-1">
                <a v-for="item in sidebarNavigation" :key="item.name" :class="[item.current ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center px-2 py-2 text-base font-medium rounded-md']"
                   :href="item.href">
                  <template v-if="sidebarPermission(item)">
                    <component :is="item.icon"
                               :class="[item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-4 flex-shrink-0 h-6 w-6']"
                               aria-hidden="true"/>
                    {{ item.name }}
                  </template>
                </a>
              </nav>
            </div>
            
            <!-- Fixed user menu at bottom of mobile sidebar -->
            <div class="flex-shrink-0 px-2 pb-4">
              <div class="border-t border-gray-200 pt-4">
                <Menu as="div" class="relative">
                  <MenuButton class="w-full flex items-center px-2 py-2 text-base font-medium text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <img alt=""
                         class="h-8 w-8 rounded-full"
                         :src="getImage($page.props.auth.user.name, $page.props.auth.user.avatar)"/>
                    <div class="ml-3 flex-1 text-left">
                      <p class="text-sm font-medium text-gray-700">{{ $page.props.auth.user.name }}</p>
                      <p class="text-xs text-gray-500">{{ $page.props.auth.user.email }}</p>
                    </div>
                    <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </MenuButton>
                  <transition enter-active-class="transition ease-out duration-100"
                              enter-from-class="transform opacity-0 scale-95"
                              enter-to-class="transform opacity-100 scale-100"
                              leave-active-class="transition ease-in duration-75"
                              leave-from-class="transform opacity-100 scale-100"
                              leave-to-class="transform opacity-0 scale-95">
                    <MenuItems class="absolute bottom-full left-0 w-full mb-1 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                      <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                        <Link :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']" 
                              :href="item.href"
                              @click="e => item.onClick ? e.preventDefault() || item.onClick() : e">
                          {{ item.name }}
                        </Link>
                      </MenuItem>
                    </MenuItems>
                  </transition>
                </Menu>
              </div>
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
          <img :alt="clientName" class="h-8 w-auto" :src="clientLogo">
          <h2 class="ml-1">{{ clientName }}</h2>
        </div>
        <!-- Scrollable navigation section -->
        <div class="mt-5 flex-1 overflow-y-auto">
          <nav class="px-2 space-y-1">
            <div v-for="(item, index) in sidebarNavigation" :key="`sidebar-${index}`">
              <template v-if="sidebarPermission(item)">
                <small v-if="item.hasOwnProperty('descriptionAbove')"
                       class="text-gray-600 font-bold font-medium px-2">{{ item.descriptionAbove }}</small>
                <Link :class="[item.current ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900', 'group flex items-center px-2 py-2 text-sm font-medium rounded-md']"
                      :href="item.href">
                  <component :is="item.icon"
                             :class="[item.current ? 'text-gray-500' : 'text-gray-400 group-hover:text-gray-500', 'mr-3 flex-shrink-0 h-6 w-6']"
                             aria-hidden="true"/>
                  {{ item.name }}
                </Link>
              </template>
            </div>
          </nav>
        </div>
        
        <!-- Fixed user menu at bottom of sidebar -->
        <div class="flex-shrink-0 px-2 pb-4">
          <div class="border-t border-gray-200 pt-4">
            <Menu as="div" class="relative">
              <MenuButton class="w-full flex items-center px-2 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <img alt=""
                     class="h-8 w-8 rounded-full"
                     :src="getImage($page.props.auth.user.name, $page.props.auth.user.avatar)"/>
                <div class="ml-3 flex-1 text-left">
                  <p class="text-sm font-medium text-gray-700">{{ $page.props.auth.user.name }}</p>
                  <p class="text-xs text-gray-500">{{ $page.props.auth.user.email }}</p>
                </div>
                <svg class="ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </MenuButton>
              <transition enter-active-class="transition ease-out duration-100"
                          enter-from-class="transform opacity-0 scale-95"
                          enter-to-class="transform opacity-100 scale-100"
                          leave-active-class="transition ease-in duration-75"
                          leave-from-class="transform opacity-100 scale-100"
                          leave-to-class="transform opacity-0 scale-95">
                <MenuItems class="absolute bottom-full left-0 w-full mb-1 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none">
                  <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                    <Link :class="[active ? 'bg-gray-100' : '', 'block px-4 py-2 text-sm text-gray-700']" 
                          :href="item.href"
                          @click="e => item.onClick ? e.preventDefault() || item.onClick() : e">
                      {{ item.name }}
                    </Link>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>
      </div>
    </div>
    <!-- Mobile menu button -->
    <div class="md:hidden fixed top-4 left-4 z-50">
      <button class="p-2 rounded-md text-gray-500 hover:text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500 bg-white shadow-md"
              type="button"
              @click="sidebarOpen = true">
        <span class="sr-only">Open sidebar</span>
        <MenuAlt2Icon aria-hidden="true" class="h-6 w-6"/>
      </button>
    </div>

    <div class="md:pl-64 flex flex-col flex-1">
      <main class="flex-1">
        <div class="py-6 container">
          <div class="w-full px-1 sm:px-6 md:px-8 pb-2">
            <slot name="header">
              <h1 class="text-2xl font-semibold text-gray-900">{{ title }}</h1>
            </slot>
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
import {computed, ref, toRefs} from 'vue'
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
import { MenuAlt2Icon, XIcon} from '@heroicons/vue/outline'
import {Head as InertiaHead, Link, usePage} from '@inertiajs/inertia-vue3'
import useNavigationStore from '@/Store/navigation'
import {storeToRefs} from 'pinia'
import logo from '@images/logo.png'
import Notification from "@/Components/Notification";

const props = defineProps({
  title: {
    type: String,
    required: false,
    default: '',
  }
})

const user = computed(() => usePage().props.value.auth.user)
const client = computed(() => usePage().props.value.client)
const clientName = computed(() => client.value?.name || 'IONBEC')
const clientLogo = computed(() => client.value?.logo_url || logo)

const navigationStore = useNavigationStore()

const {sidebarNavigation, userNavigation} = storeToRefs(navigationStore)

const {title} = toRefs(props)

const sidebarOpen = ref(false)
const closeSidebar = () => sidebarOpen.value = false

const getImage = (name, avatar) => {
  let image = 'https://ui-avatars.com/api/?background=4f46e5&color=fff&&name=' + encodeURIComponent(name);
  if (avatar) image = window.location.origin + '/storage/profile/' + avatar;

  return image;
}

const getRoles = () => {
  if (user.value.roles?.length >= 1) {
    return user.value.roles.map((role) => role.slug)
  }
  return [];
}
const sidebarPermission = (item) => !item.hasOwnProperty('allowed') || (item.hasOwnProperty('allowed') && item.allowed.some((allow) => getRoles().includes(allow)))
</script>
