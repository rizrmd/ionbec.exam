<script setup>

import {ref, toRefs} from "vue";
import {Head as InertiaHead} from '@inertiajs/inertia-vue3';
import moment from 'moment';
import Notification from "../../Components/Notification.vue";
import Message from "../../Components/Message.vue";

const props = defineProps({
  delivery: {
    type: Object,
    default: null,
  },
  lastLive: {
    type: Object,
    default: {
      question: null,
      attempt: null,
    }
  }
})

const {delivery} = toRefs(props)

const liveQuestion = ref(props.lastLive.question)
const liveAttempt = ref(props.lastLive.attempt)

window.Echo.private(`live-interview.${delivery.value.hash}`)
  .listen('LiveInterviewEvent', (e) => {
    let questionEl = document.getElementById('question-container')
    if (questionEl !== null) questionEl.classList.add('animate-ping')

    console.log("RECEIVED", e)

    setTimeout(() => {
      liveQuestion.value = e.question
      liveAttempt.value = e.attempt

      if (questionEl !== null) questionEl.classList.remove('animate-ping')
    }, 800)
  });

</script>

<template>
  <InertiaHead title="Live Interview"/>

  <div class="flex items-center justify-center min-w-screen min-h-screen">
    <div class="fixed top-0 left-0 min-w-full backdrop-blur-md bg-white/30 z-10">
      <div class="flex justify-between items-center mx-5 my-2">
        <div class="flex-1 hidden md:block">
          Live Interview
          -
          <span class="text-sm">{{ delivery.display_name }}</span>
        </div>
      </div>
    </div>

    <div class="container mx-auto my-auto absolute pt-24 pb-12 px-4 min-h-screen">
      <div v-if="liveQuestion !== null && liveAttempt !== null" class="grid grid-cols-6 gap-4">
        <div class="col-span-2">
          <Message message="If the information shown here is incorrect, please report it to interviewer or admin." />
          <div class="mt-4">
            <label class="text-black font-semibold">Session Detail</label>
            <p class="text-sm text-slate-500'">{{ liveAttempt.delivery.name }}</p>
            <span class="text-sm text-slate-500">{{ moment(liveAttempt.scheduled_at).format('DD MMM YYYY') }}</span>
          </div>
          <div class="mt-4">
            <label class="text-black font-semibold">Reg. No</label>
            <p class="text-sm text-slate-500'">{{ liveAttempt.taker.reg }}</p>
          </div>
          <div class="mt-4">
            <label class="text-black font-semibold">Interviewee Name</label>
            <p class="text-sm text-slate-500'">{{ liveAttempt.taker.name }}</p>
          </div>
        </div>
        <div class="col-span-4">
          <div class="mb-4">
            <span class="px-2 py-0.5 text-xs font-mono text-slate-600 bg-slate-50 border border-slate-600 rounded-md">
              {{ liveQuestion.hash }}
            </span>
          </div>
          <div id="question-container" class="px-12 py-8 text-center text-2xl bg-white border border-slate-200 rounded-md"
               v-html="liveQuestion.question"></div>
        </div>
      </div>
      <div v-else>
        <div class="text-center">
          <h2 class="text-2xl text-slate-800">No attempter information</h2>
          <p class="text-sm text-slate-500">Interview not started yet</p>
        </div>
      </div>
    </div>
  </div>

  <Notification/>
</template>

<style scoped>

</style>
