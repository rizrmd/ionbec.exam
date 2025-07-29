<script setup>
import {Head, Link, useForm} from '@inertiajs/inertia-vue3';
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

const form = useForm({
  username: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.transform(data => ({
    ...data,
    remember: form.remember ? 'on' : '',
  })).post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <Head title="Sign in"/>

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
      <div class="text-2xl font-bold mt-2 mb-4 text-center">User Sign In</div>
      <div>
        <JetLabel for="username" value="Username"/>
        <input type="text" id="username" autocomplete="username" v-model="form.username" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required autofocus/>
      </div>

      <div class="mt-4">
        <JetLabel for="password" value="Password"/>
        <input type="password" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" v-model="form.password" autocomplete="current-password" id="password">
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
