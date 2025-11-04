<script setup>
import {Head, Link, useForm, usePage} from '@inertiajs/inertia-vue3';
import {computed, ref} from 'vue';
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetAuthenticationCardLogo from '@/Jetstream/AuthenticationCardLogo.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetCheckbox from '@/Jetstream/Checkbox.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';
import Input from "@/Jetstream/Input";

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const client = computed(() => usePage().props.value.client)
const clientName = computed(() => client.value?.name || 'National Orthopaedic and Traumatology Board Examination')
const clientLogo = computed(() => client.value?.logo_url || '/images/logo.png')

// Get errors and flash messages from page props
const errors = computed(() => usePage().props.value.errors)
const hasErrors = computed(() => Object.keys(errors.value).length > 0)
const flash = computed(() => usePage().props.value.flash)

const form = useForm({
  username: '',
  password: '',
  remember: false,
});

const showPassword = ref(false);

const submit = () => {
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onFinish: () => form.reset('password'),
    onError: (errors) => {
      console.error('Login errors:', errors);
    },
    onSuccess: () => {
      console.log('Login successful');
    },
  });
};
</script>

<template>
  <Head title="Sign in"/>

  <JetAuthenticationCard>
    <template #logo>
      <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
        <a href="/">
          <img :alt="clientName" :src="clientLogo">
        </a>
      </div>
    </template>

    <JetValidationErrors class="mb-4"/>

    <!-- Flash error messages -->
    <div v-if="flash?.error" class="mb-4 font-medium text-sm text-red-600">
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        {{ flash.error }}
      </div>
    </div>

    <!-- Custom error display for debugging -->
    <div v-if="hasErrors" class="mb-4 font-medium text-sm text-red-600">
      <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
        <strong>Login Error:</strong>
        <ul class="mt-1 list-disc list-inside">
          <li v-for="(error, key) in errors" :key="key">{{ error }}</li>
        </ul>
      </div>
    </div>

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div class="text-2xl font-bold mt-2 mb-4 text-center">{{ clientName }} - User Sign In</div>
      <div>
        <JetLabel for="username" value="Username"/>
        <input type="text" id="username" autocomplete="username" v-model="form.username" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required autofocus/>
      </div>

      <div class="mt-4">
        <JetLabel for="password" value="Password"/>
        <div class="relative">
          <input
            :type="showPassword ? 'text' : 'password'"
            class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md pr-10"
            v-model="form.password"
            autocomplete="current-password"
            id="password"
          >
          <button
            type="button"
            @click="showPassword = !showPassword"
            class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1"
          >
            <svg
              v-if="showPassword"
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-gray-500 hover:text-gray-700"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"
              />
            </svg>
            <svg
              v-else
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-gray-500 hover:text-gray-700"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
              />
            </svg>
          </button>
        </div>
      </div>

      <div class="block mt-4">
        <label class="flex items-center">
          <JetCheckbox v-model:checked="form.remember" name="remember"/>
          <span class="ml-2 text-sm text-gray-600">Remember me</span>
        </label>
      </div>

      <div class="flex items-center justify-end mt-4">
<!--        <Link v-if="canResetPassword" :href="route('password.request')"-->
<!--              class="underline text-sm text-gray-600 hover:text-gray-900">-->
<!--          Forgot your password?-->
<!--        </Link>-->

        <button :class="[form.processing ? 'opacity-25' : '', 'py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 ml-4']" type="submit" :disabled="form.processing">
          Log in
        </button>
      </div>
    </form>
  </JetAuthenticationCard>
</template>
