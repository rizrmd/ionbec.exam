<script setup>
import {ChevronLeftIcon} from '@heroicons/vue/outline'
import {Head as InertiaHead, Link} from '@inertiajs/inertia-vue3'

const props = defineProps({
  taker: {
    type: Object,
  },
  delivery: {
    type: Object,
  }
})

</script>

<template>
  <InertiaHead title="Finished"/>
  <div class="container mx-auto pt-20 pb-12 px-4 min-h-screen">
    <div class="flex flex-col items-top justify-center min-w-full min-h-full text-center">
      <div class="flex justify-center pt-8">
        <img alt="" src="/images/logo.png">
      </div>

      <div>
        <h2>National Orthopaedic and Traumatology Board Examination</h2>
      </div>

      <div class="mt-10">
        Thank you, <span class="font-bold">{{ taker.name }}</span>
      </div>

      <div class="mt-5 text-gray-500">
        For completing <span class="text-black">{{ delivery.name }}</span>
      </div>

      <div class="mt-10">
        <Link :href="route('exam.logout')" class="flex justify-center items-center text-primary-600">
          <ChevronLeftIcon aria-hidden="true" class="h-5 w-5 mr-2"/> <span>Back to home page</span>
        </Link>
      </div>
    </div>
  </div>
</template>

<style scoped>

</style>
