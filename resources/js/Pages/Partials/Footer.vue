<template>
  <div class="w-full text-center lg:text-left bg-gray-100 text-gray-600">
    <div class="text-center p-6 bg-gray-200">
      <span>© {{ year }} Copyright : </span>
      <a class="text-gray-600 font-semibold" href="/">{{ appName }}</a>
    </div>
  </div>
</template>

<script setup>
import {computed} from 'vue'
import {usePage} from '@inertiajs/inertia-vue3'

const appName = computed(() => usePage().props?.app?.name ?? 'Ionbec')
const year = computed(() => new Date().getFullYear())
</script>

<style scoped>

</style>
