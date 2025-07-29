<script setup>
import {Link} from '@inertiajs/inertia-vue3'
import route from "@/Libs/ziggy";
import {Inertia} from "@inertiajs/inertia";

const props = defineProps({
  title: {
    type: String,
    required: false,
    default: 'Exam',
  },
  taker: {
    type: Object,
  },
  timer: {
    type: String,
    default: null,
  }
})

const logout = () => {
  localStorage.removeItem('exam-state')
  Inertia.visit(route('exam.logout'))
}

</script>

<template>
  <div class="fixed top-0 left-0 min-w-full backdrop-blur-md bg-white/30 z-10">
    <div class="block md:hidden text-center p-1">
      Hello, {{ taker === undefined ? 'User' : (taker?.name ?? 'User') }}
      -
      <span class="text-sm">{{ title }}</span>
    </div>
    <div class="flex justify-between items-center mx-5 my-2">
      <div class="flex-1 hidden md:block">
        Hello, {{ taker === undefined ? 'User' : (taker?.name ?? 'User') }}
        -
        <span class="text-sm">{{ title }}</span>
      </div>
      <div class="flex-1 flex justify-center items-center">
        <div v-if="timer !== null" class="text-center px-2 py-1.5">
          <span class="text-black text-lg">Time Remaining<br/><span class="font-semibold text-2xl">{{ timer }}</span></span>
        </div>
      </div>
      <div class="flex-1 flex flex-row justify-end items-center">
        <button type="button" @click="() => logout()" class="flex justify-center items-center text-white bg-red-600 rounded-md py-1 px-4 hover:bg-red-700">
          Logout
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>
