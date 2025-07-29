<script setup>
import {Head, Link, useForm, usePage} from '@inertiajs/inertia-vue3';
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import {computed} from "vue";
import Notification from "@/Components/Notification";

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
});

const form = useForm({
  token: null,
});

const loginExam = () => {
  form.post(route('exam.login'))
}


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
  if (userRoles.value.includes('administrator')) {
    return route('back-office.dashboard')
  } else {
    return route('back-office.scoring.index');
  }
}
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
        <Link :href="route('taker.register')" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          Register
        </Link>
        <Link :href="route('taker.login')" class="py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
          Login
        </Link>
      </div>
    </div>

    <div class="max-w-6xl sm:px-6 lg:px-8">
      <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
        <img alt="" src="/images/logo.png">
      </div>
    </div>

    <div>
      <h2>National Orthopaedic and Traumatology Board Examination</h2>
    </div>

    <div class="shadow ring-1 ring-black ring-opacity-5 rounded-lg bg-white mt-8 p-3">
      <JetValidationErrors class="my-4"/>
      <div class="flex flex-row">
        <input id="search-name"
               v-model="form.token"
               class="inline-flex focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-l-md"
               placeholder="Insert exam-token"
               type="text">
        <button
          @click="loginExam()"
          class="inline-flex px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-r-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          type="button">
          Submit
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
