<template>
  <div :class="['rounded-md bg-yellow-50 px-2.5 py-1.5 text-sm font-medium text-yellow-600']">
    <ExclamationIcon class="inline w-4 h-4 mr-1"/> {{ message }}
  </div>
</template>

<script setup>
import {XIcon, CheckCircleIcon, XCircleIcon, ExclamationIcon} from '@heroicons/vue/outline'

const props = defineProps({
  message: {
    type: String,
    default: 'No message.',
  },
  type: {
    type: String,
    default: 'warning'
  },
})

</script>

<style scoped>

</style>
