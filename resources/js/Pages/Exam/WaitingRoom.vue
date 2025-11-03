<script setup>
import ExamLayout from "@/Layouts/ExamLayout";
import {computed, onMounted, ref, toRefs} from "vue";
import {usePage} from '@inertiajs/inertia-vue3';
import moment from "moment-timezone";

const props = defineProps({
  taker: {
    type: Object,
  },
  delivery: {
    type: Object,
  }
})

const {taker, delivery, payload} = toRefs(props)

const client = computed(() => usePage().props.value.client)
const clientName = computed(() => client.value?.name || 'National Orthopaedic and Traumatology Board Examination')
const clientLogo = computed(() => client.value?.logo_url || '/images/logo.png')

const isTheDay = computed(() => {
  return moment(delivery.value.scheduled_at).format('YYYY-MM-DD') === moment().format('YYYY-MM-DD');
})

const countDownDisplay = ref("00:00");
const startTimer = (duration) => {
  let timer = duration, minutes, seconds;
  console.log("Starting timer with duration:", duration, "seconds");

  let timerInterval = setInterval(function () {
    minutes = parseInt(timer / 60, 10);
    seconds = parseInt(timer % 60, 10);

    minutes = minutes < 10 ? "0" + minutes : minutes;
    seconds = seconds < 10 ? "0" + seconds : seconds;

    countDownDisplay.value = minutes + ":" + seconds;

    if (--timer < 0) {
      console.log("Timer expired, redirecting to exam...");
      clearInterval(timerInterval);
      window.location.href = '/exam';
      return;
    }

    // Reload page every 60 seconds to sync with server (but not when timer is close to 0)
    if (timer > 5 && timer % 60 === 0) {
      console.log("Reloading page for sync...");
      clearInterval(timerInterval);
      window.location.reload();
    }

  }, 1000);
}

onMounted(() => {
  let todayDatetime = moment(new Date()).tz(moment.tz.guess());
  let scheduledAt = moment(delivery.value.scheduled_at + "+07:00");

  console.log("TODAY:", todayDatetime.format("YYYY-MM-DD HH:mm:ss Z"));
  console.log("SCHEDULED:", scheduledAt.format("YYYY-MM-DD HH:mm:ss Z"));

  let duration = moment.duration(scheduledAt.diff(todayDatetime));
  let durationInSeconds = Math.round(duration.asSeconds());

  console.log("Duration in seconds:", durationInSeconds);

  // If exam time has already passed or is now, redirect immediately
  if (durationInSeconds <= 0) {
    console.log("Exam time has passed, redirecting immediately...");
    window.location.href = '/exam';
    return;
  }

  startTimer(durationInSeconds)
})

</script>

<template>
  <ExamLayout :title="delivery.name" :taker="taker">
    <div class="flex items-center justify-center min-w-full min-h-full px-4">
      <div class="w-full max-w-md bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Card Content -->
        <div class="p-8">
          <!-- Organization Title -->
          <div class="mb-8">
            <h1 class="text-xl font-bold text-gray-800 text-center leading-tight">
              {{ clientName }}
            </h1>
          </div>

          <!-- Avatar Section -->
          <div class="flex justify-center mb-6">
            <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center">
              <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
            </div>
          </div>

          <!-- Examinee Name -->
          <div class="text-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800 uppercase tracking-wide">
              {{ taker.name }}
            </h2>
          </div>

          <!-- Warning Text -->
          <div class="text-center mb-8">
            <p class="text-amber-600 text-sm font-medium">
              If you are not the above named examinee, please contact the invigilator now.
            </p>
          </div>

          <!-- Exam Date/Time Countdown -->
          <div class="bg-gray-50 rounded-lg p-6 mb-8">
            <div class="text-center">
              <div class="text-sm text-gray-600 mb-2">Exam Date & Time</div>
              <div class="text-lg font-bold text-gray-800" v-if="isTheDay">
                {{ countDownDisplay }}
              </div>
              <div class="text-lg font-bold text-gray-800" v-else>
                {{ moment(delivery.scheduled_at).format('MMMM Do YYYY, h:mm A') }}
              </div>
            </div>
          </div>

          <!-- Exam Name -->
          <div class="text-center">
            <div class="text-lg font-bold text-blue-600 uppercase tracking-wide">
              {{ delivery.name }}
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 px-8 py-4 text-center border-t">
          <p class="text-xs text-gray-500">
            COPYRIGHT © 2025 NATIONAL ORTHOPAEDIC AND TRAUMATOLOGY BOARD EXAMINATION
          </p>
        </div>
      </div>
    </div>
  </ExamLayout>
</template>

<style scoped>

</style>
