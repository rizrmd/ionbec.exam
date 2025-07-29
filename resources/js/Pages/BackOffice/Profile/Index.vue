
<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {computed, toRefs, ref} from "vue";
import Card from '@/Components/Card.vue'
import Datepicker from '@/Components/Datepicker.vue'
import {useForm} from "@inertiajs/inertia-vue3";
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import {PencilIcon} from '@heroicons/vue/outline'

const props = defineProps({
  auth: {
    type: Object,
  }
})

const {auth} = toRefs(props)

const name = computed(() => {
  return auth.value?.user?.name
})

const form = useForm({
  name: null,
  email: null,
  username: null,
  birthday: null,
  gender: null,
})

const user = auth.value.user;
form.name = user.name
form.email = user.email
form.username = user.username
form.birthday = user.birthday
form.gender = user.gender

const updateBiodata = () => {
  if (form.birthday !== null) {
    const birthD = form.birthday.split('-')
    form.birthday = `${birthD[0]}-${(birthD[1].length === 1) ? "0" + birthD[1] : birthD[1]}-${birthD[2]}`;
  }
  form.post(route('back-office.profile.update-biodata'))
}

const formPassword = useForm({
  password: null,
  password_confirmation: null,
})

const updatePassword = () => {
  formPassword.post(route('back-office.profile.update-password'))
}


const myFile = ref(null);
const fileChanged = () => {
  if (myFile.value.files.length >= 1) {
    const image = myFile.value.files[0];
    const formImage = useForm({
      image: image,
    })

    formImage.post(route('back-office.profile.update-image'), {
      onSuccess: () => window.location.reload()
    });
  }
}

const avatar = ref('https://ui-avatars.com/api/?background=4f46e5&color=fff&&name=' + encodeURIComponent(auth.value.user.name));
if (auth.value.user.avatar) avatar.value = window.location.origin + '/storage/profile/' + auth.value.user.avatar;
</script>

<template>
  <dashboard-layout title="My Profile">
    <div class="flex justify-center items-center flex-col">
      <div class="flex justify-center items-center gap-3">
        <div class="group rounded-full overflow-hidden relative bg-black cursor-pointer">
          <input type="file" accept="image/*" :ref="el => { myFile = el }" style="display: none;" @change="fileChanged" />
          <PencilIcon aria-hidden="true" class="h-5 w-5 absolute top-0 bottom-0 right-0 left-0 m-auto hidden group-hover:block text-white"/>
          <img alt="" class="h-16 w-16 group-hover:opacity-25" :src="avatar" @click="myFile.click()"/>
        </div>
        <div>
          <div>{{ auth.user.name }} ({{ auth.user.username }})</div>
          <div class="text-sm text-gray-600">{{ auth.user.email }}</div>
        </div>
      </div>

      <JetActionMessage :on="form.recentlySuccessful || formPassword.recentlySuccessful" class="my-2" />
      <JetValidationErrors class="my-4"/>

      <div class="flex gap-3 mt-10 w-full">
        <card class="flex-1 overflow-visible">
          <div class="text-lg mb-2">Biodata</div>
          <input
            class="w-full mt-4 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            placeholder="Name"
            v-model="form.name"
            type="text">
          <input
            class="w-full mt-4 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            placeholder="Email"
            v-model="form.email"
            type="email">
          <input
            class="w-full mt-4 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            placeholder="Username"
            v-model="form.username"
            type="text">
          <div class="mt-5">
            <label class="inline-flex items-center">
              <input type="radio" class="form-radio" value="male" v-model="form.gender">
              <span class="ml-2">Male</span>
            </label>
            <label class="inline-flex items-center ml-6">
              <input type="radio" class="form-radio" value="female" v-model="form.gender">
              <span class="ml-2">Female</span>
            </label>
            <label class="inline-flex items-center ml-6">
              <input type="radio" class="form-radio" value="other" v-model="form.gender">
              <span class="ml-2">Other</span>
            </label>
          </div>
          <div class="mt-4">
            <Datepicker v-model="form.birthday" placeholder="Select your birth date" :enable-time="false" position-y="top"/>
          </div>
          <div class="flex justify-end mt-4">
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

          <div class="flex justify-end mt-4">
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

    </div>
  </dashboard-layout>
</template>


<style scoped>

</style>
