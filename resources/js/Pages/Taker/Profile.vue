<script setup>
import TakerLayout from '@/Layouts/TakerLayout.vue'
import {toRefs} from "vue";
import Card from '@/Components/Card.vue'
import {useForm} from "@inertiajs/inertia-vue3";
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetValidationErrors from '@/Jetstream/ValidationErrors'

const props = defineProps({
  taker: {
    type: Object
  },
});

const {taker} = toRefs(props)

const form = useForm({
  name: taker.value.name,
})

const updateBiodata = () => form.post(route('taker.profile.update-biodata'))

const formPassword = useForm({
  password: null,
  password_confirmation: null,
})

const updatePassword = () => formPassword.post(route('taker.profile.update-password'))
</script>

<template>
  <taker-layout title="Profile" :info-card="false">
    <JetActionMessage :on="form.recentlySuccessful" class="my-2" />
    <JetValidationErrors class="my-4"/>

    <div class="flex gap-3 mt-10 w-full">
      <card class="flex-1 overflow-visible">
        <div class="text-lg mb-2">Biodata</div>
        <div class="mt-4">
          <label for="email" class="block text-sm font-medium text-gray-700">
            Email
          </label>
          <input
            class="w-full mt-1 block w-full shadow-sm sm:text-sm border-gray-300 bg-gray-50 text-gray-500 rounded-md"
            placeholder="Email"
            type="email"
            id="email"
            :value="taker.email"
            disabled>
        </div>

        <div class="mt-4">
          <label for="name" class="block text-sm font-medium text-gray-700 mt-4">
            Name
          </label>
          <input
            class="w-full mt-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            placeholder="Name"
            id="name"
            v-model="form.name"
            type="text">
        </div>

        <div class="flex justify-end mt-2">
          <button
            class="py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            type="submit"
            :disabled="form.processing"
            @click="updateBiodata()">
            Save
          </button>
        </div>
      </card>
      <card class="flex-1">
        <div class="text-lg mb-2">Password</div>
        <p class="text-orange-500 text-sm">After you update the password, you will be logged out, please log back in!</p>
        <input
          class="w-full mt-4 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
          placeholder="New Password"
          v-model="formPassword.password"
          type="password">
        <input
          class="w-full mt-4 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
          placeholder="Confirm New Password"
          v-model="formPassword.password_confirmation"
          type="password">

        <div class="flex justify-end mt-2">
          <button
            class="py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            type="submit"
            :disabled="formPassword.processing"
            @click="updatePassword()">
            Update
          </button>
        </div>
      </card>
    </div>
  </taker-layout>
</template>


