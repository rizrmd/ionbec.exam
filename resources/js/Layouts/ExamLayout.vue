<script setup>
import {Head as InertiaHead} from '@inertiajs/inertia-vue3';
import ExamHeader from "@/Layouts/Partials/Exam/ExamHeader.vue";
import Notification from "@/Components/Notification.vue";

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

</script>

<template>
  <InertiaHead :title="title ?? 'Test'"/>

  <div class="flex items-center justify-center min-w-screen min-h-screen">
    <ExamHeader :title="title" :taker="taker" :timer="timer"/>

    <div class="container mx-auto pt-24 pb-12 px-4 min-h-screen">
      <slot/>
    </div>
  </div>
  <Notification/>
</template>
