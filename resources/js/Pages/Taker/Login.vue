<script setup>
import {Head, Link, useForm} from '@inertiajs/inertia-vue3';
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';
import Input from "@/Jetstream/Input";

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: ''
});

const submit = () => {
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('taker.sign-in'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="Candidate Sign In"/>

  <JetAuthenticationCard>
    <template #logo>
      <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
        <a href="/">
          <img alt="" src="/images/logo.png">
        </a>
      </div>
    </template>

    <JetValidationErrors class="mb-4"/>

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <h2 class="text-2xl font-bold mt-2 mb-4 text-center">Sign In as Participant</h2>
      <div>
        <JetLabel for="email" value="Email"/>
        <input type="text" id="email" autocomplete="email" v-model="form.email" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required autofocus/>
      </div>

      <div class="mt-4">
        <JetLabel for="password" value="Password"/>
        <input type="password" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" v-model="form.password" autocomplete="current-password" id="password">
      </div>

      <div class="flex items-center justify-between mt-8">
        <Link :href="route('login')" class="text-blue-600 hover:text-blue-800 text-sm">
          Sign in as Committee
        </Link>
        <button :class="[form.processing ? 'opacity-25' : '', 'py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 ml-4']" type="submit" :disabled="form.processing">
          Log in
        </button>
      </div>
    </form>
  </JetAuthenticationCard>
</template>
