<script setup>
import {Head, Link, useForm} from '@inertiajs/inertia-vue3';
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';
import Input from "@/Jetstream/Input";
import Notification from "@/Components/Notification";
import Datepicker from "@/Components/Datepicker";
import Select from "@/Components/Select";
import {computed, ref, watch} from "vue";

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  group: null,
});

const submit = () => {
  if (form.group !== null) form.group = form.group.value
  form.post(route('taker.sign-up'))
};

const withGroup = ref(false);
const selectedOptions = ref([]);

watch(withGroup, function (newVal, prevVal) {
  selectedOptions.value = []
  form.group = null;
  axios.post(route('taker.register.groups'))
    .then((res) => {
      const data = res.data;
      data.forEach((group) => selectedOptions.value.push({
        text: group.name,
        value: group.hash,
      }))
    })
    .catch((err) => console.log(err))
})
</script>

<template>
  <Head title="Participant Sign-up"/>

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
      <div class="text-2xl font-bold mt-2 mb-4 text-center">Candidate Sign Up</div>

      <div>
        <JetLabel for="name" value="Name"/>
        <input type="text" id="name" v-model="form.name" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required autofocus/>
      </div>

      <div class="mt-4">
        <JetLabel for="email" value="Email"/>
        <input type="text" id="email" autocomplete="email" v-model="form.email" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required/>
      </div>

      <div class="mt-4">
        <JetLabel for="password" value="Password"/>
        <input type="password" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" v-model="form.password" id="password">
      </div>

      <div class="mt-4">
        <JetLabel for="password_confirmation" value="Password Confirmation"/>
        <input type="password" class="mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" v-model="form.password_confirmation" id="password_confirmation">
      </div>

      <div class="mt-4 sm:col-span-2 flex items-center gap-2">
        <input id="with_group" type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" v-model="withGroup">
        <label for="with_group">
          Already know Exam Group?
        </label>
      </div>

      <template v-if="withGroup">
        <div class="mt-4">
          <JetLabel value="Select Group"/>
          <Select :options="selectedOptions" title="Select Group" v-model="form.group" search-placeholder="Search Group" class="mt-1"/>
        </div>
      </template>

      <div class="mt-4 text-gray-600 text-sm">
        You'll not be able to sign-in immediately after registration, admin verification is needed. If you not able to sign-in within 24 hour, please contact the administrator.
      </div>

      <div class="flex items-center justify-end mt-8">
        <button :class="[form.processing ? 'opacity-25' : '', 'py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 ml-4']" type="submit" :disabled="form.processing">
          Submit
        </button>
      </div>
    </form>
  </JetAuthenticationCard>
  <Notification/>
</template>
