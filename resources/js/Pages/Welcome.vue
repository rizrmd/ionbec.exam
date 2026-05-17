<script setup>
import {Head, Link, useForm, usePage} from '@inertiajs/inertia-vue3';
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import {computed, onMounted} from "vue";
import Notification from "@/Components/Notification";
import { notification } from "@/Store/notification";

const props = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
  client: Object,
});

const form = useForm({
  token: null,
});

const loginExam = () => {
  if (form.processing) return
  form.token = typeof form.token === 'string' ? form.token.trim().toUpperCase() : form.token
  form.post(route('exam.login'))
}

// Flash message handling
const flashMessage = computed(() => usePage().props.value.flash?.error || usePage().props.value.flash?.success)
const flashType = computed(() => {
  if (usePage().props.value.flash?.error) return 'error'
  if (usePage().props.value.flash?.success) return 'success'
  return null
})

const user = computed(() => usePage().props.value.auth.user)
const taker = computed(() => usePage().props.value.auth.taker)
const userRoles = computed( () => {
  if (user) {
    return user.value.roles.map((role) => role.slug);
  } else {
    return [];
  }
})

const getUserRoute = () => {
  if (userRoles.value.includes('root')) {
    return route('back-office.root-dashboard')
  } else if (userRoles.value.includes('administrator')) {
    return route('back-office.dashboard')
  } else {
    return route('back-office.scoring.index');
  }
}

const getRegisterRoute = () => {
  return route('taker.register')
}

const getLoginRoute = () => {
  return route('taker.login')
}

// Show flash message when component mounts
onMounted(() => {
  if (flashMessage.value && flashType.value) {
    // Use the notification system to show flash messages
    notification.add(flashType.value, 'Exam Token', flashMessage.value)
  }
})
</script>

<template>
  <Head title="Welcome"/>

  <div
    class="flex flex-col items-top justify-center min-h-screen bg-gray-100 sm:items-center sm:pt-0 h-screen">
    <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
      <div class="flex gap-2" v-if="user || taker">
        <Link v-if="user" :href="getUserRoute()" class="text-sm text-gray-700 underline">
          Dashboard
        </Link>
        <Link v-if="taker" :href="route('taker.dashboard')" class="text-sm text-gray-700 underline">
          Dashboard
        </Link>
      </div>
      <div class="flex gap-2" v-else>
        <Link :href="getRegisterRoute()" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Register
        </Link>
        <Link :href="getLoginRoute()" class="py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
          Login
        </Link>
      </div>
    </div>

    <div class="max-w-6xl sm:px-6 lg:px-8">
      <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
        <img alt="" :src="props.client?.logo_url || '/images/logo.png'">
      </div>
    </div>

    <div>
      <h2>{{ props.client?.name || 'National Orthopaedic and Traumatology Board Examination' }}</h2>
    </div>

    <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg bg-white mt-8 p-3">
      <JetValidationErrors class="my-4"/>

      <!-- Flash message display -->
      <div v-if="flashMessage" class="mb-4 p-4 rounded-md" :class="{
        'bg-red-50 border border-red-200 text-red-800': flashType === 'error',
        'bg-green-50 border border-green-200 text-green-800': flashType === 'success'
      }">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg v-if="flashType === 'error'" class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <svg v-else class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium">{{ flashMessage }}</p>
          </div>
        </div>
      </div>
      <div class="flex flex-row">
        <input id="search-name"
               v-model="form.token"
               @keydown.enter="loginExam()"
               class="focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-l-md"
               placeholder="Insert exam-token"
               type="text">
        <button
          @click="loginExam()"
          :disabled="form.processing"
          :class="[
            'inline-flex px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-r-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500',
            form.processing ? 'bg-gray-400 cursor-not-allowed' : 'bg-primary-600 hover:bg-primary-700'
          ]"
          type="button">
          {{ form.processing ? 'Submitting...' : 'Submit' }}
        </button>
      </div>
      <div class="text-gray-600 text-sm mt-2">
        Insert exam-token to start your exam.
      </div>
    </div>

  </div>
  <Notification :default-timeout="30"/>
</template>

<style scoped>
.bg-gray-100 {
  background-color: #f7fafc;
  background-color: rgba(247, 250, 252, var(--tw-bg-opacity));
}

.border-gray-200 {
  border-color: #edf2f7;
  border-color: rgba(237, 242, 247, var(--tw-border-opacity));
}

.text-gray-400 {
  color: #cbd5e0;
  color: rgba(203, 213, 224, var(--tw-text-opacity));
}

.text-gray-500 {
  color: #a0aec0;
  color: rgba(160, 174, 192, var(--tw-text-opacity));
}

.text-gray-600 {
  color: #718096;
  color: rgba(113, 128, 150, var(--tw-text-opacity));
}

.text-gray-700 {
  color: #4a5568;
  color: rgba(74, 85, 104, var(--tw-text-opacity));
}

.text-gray-900 {
  color: #1a202c;
  color: rgba(26, 32, 44, var(--tw-text-opacity));
}

@media (prefers-color-scheme: dark) {
  .dark\:bg-gray-800 {
    background-color: #2d3748;
    background-color: rgba(45, 55, 72, var(--tw-bg-opacity));
  }

  .dark\:bg-gray-900 {
    background-color: #1a202c;
    background-color: rgba(26, 32, 44, var(--tw-bg-opacity));
  }

  .dark\:border-gray-700 {
    border-color: #4a5568;
    border-color: rgba(74, 85, 104, var(--tw-border-opacity));
  }

  .dark\:text-white {
    color: #fff;
    color: rgba(255, 255, 255, var(--tw-text-opacity));
  }

  .dark\:text-gray-400 {
    color: #cbd5e0;
    color: rgba(203, 213, 224, var(--tw-text-opacity));
  }
}
</style>
