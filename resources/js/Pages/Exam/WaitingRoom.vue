<script setup>
import ExamLayout from "@/Layouts/ExamLayout";
import {computed, onMounted, ref, toRefs} from "vue";
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

const isTheDay = computed(() => {
  return moment(delivery.value.scheduled_at).format('YYYY-MM-DD') === moment().format('YYYY-MM-DD');
})

const countDownDisplay = ref("00:00");
const startTimer = (duration) => {
  let timer = duration, minutes, seconds;
  // console.log("TIMER", duration, minutes, seconds);
  let timerInterval = setInterval(function () {
    minutes = parseInt(timer / 60, 10);
    seconds = parseInt(timer % 60, 10);

    minutes = minutes < 10 ? "0" + minutes : minutes;
    seconds = seconds < 10 ? "0" + seconds : seconds;

    countDownDisplay.value = minutes + ":" + seconds;

    if (--timer < 0) {
      timer = duration;
      clearInterval(timerInterval);
      window.location.href = '/exam';
    } else if (timer % 15 === 0) {
      clearInterval(timerInterval);
      window.location.reload();
    }

  }, 1000);
}

onMounted(() => {
  let todayDatetime = moment(new Date()).tz(moment.tz.guess());
  let scheduledAt = moment(delivery.value.scheduled_at + "+07:00");

  // console.log("TODAY", todayDatetime.format("D-M-Y H:m:s z"))
  // console.log("SCHEDULE", scheduledAt.format("D-M-Y H:m:s z"))

  let duration = moment.duration(scheduledAt.diff(todayDatetime));
  startTimer(Math.round(duration.asSeconds()))
  // console.log(scheduledAt.format('YYYY-MM-DD H:mm:ss'), todayDatetime.format('YYYY-MM-DD H:mm:ss'))
})

</script>

<template>
  <ExamLayout :title="delivery.name" :taker="taker">
    <div class="flex flex-col items-top justify-center min-w-full min-h-full text-center">
      <div class="max-w-full sm:px-6 lg:px-8">
        <div class="flex justify-center pt-8">
          <img alt="" src="/images/logo.png">
        </div>
      </div>

      <div>
        <h2>National Orthopaedic and Traumatology Board Examination</h2>
      </div>

      <div class="mt-10">
        Hello, <span class="font-bold">{{ taker.name }}</span>
      </div>

      <div class="mt-7">
        {{ delivery.name }}
      </div>

      <div class="mt-7 text-gray-500">
        If it’s not your name, please report to the organizer.
      </div>

      <div class="mt-10">
        <div class="font-bold mb-7">
          <div>Exam will start in</div>
          <div class="text-9xl" v-if="isTheDay">{{ countDownDisplay }}</div>
          <div class="text-4xl" v-else>{{ moment(delivery.scheduled_at).format('MMMM Do YYYY') }}</div>
        </div>
        <div>Please wait, exam will automatically starts when the time run out.</div>
      </div>
    </div>
  </ExamLayout>
</template>

<style scoped>

</style>
