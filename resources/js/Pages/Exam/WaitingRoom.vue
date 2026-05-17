<script setup>
import ExamLayout from "@/Layouts/ExamLayout";
import {computed, onBeforeUnmount, onMounted, ref, toRefs} from "vue";
import {usePage} from '@inertiajs/inertia-vue3';
import moment from "moment-timezone";
import route from "@/Libs/ziggy";

const props = defineProps({
  taker: {
    type: Object,
  },
  delivery: {
    type: Object,
  },
  status: {
    type: Object,
    default: () => ({}),
  }
})

const {taker, delivery, status} = toRefs(props)

const client = computed(() => usePage().props.value.client)
const clientName = computed(() => client.value?.name || 'National Orthopaedic and Traumatology Board Examination')
const clientLogo = computed(() => client.value?.logo_url || '/images/logo.png')
const currentStatus = ref(status.value || {})
let pollTimeout = null

const scheduledDisplay = computed(() => {
  const scheduledAt = currentStatus.value?.scheduled_at || delivery.value.scheduled_at
  return scheduledAt ? moment(scheduledAt).format('DD MMMM YYYY, HH:mm') : '-'
})

const clearPoll = () => {
  if (pollTimeout) {
    clearTimeout(pollTimeout)
    pollTimeout = null
  }
}

const schedulePoll = (seconds = 15) => {
  clearPoll()
  pollTimeout = setTimeout(fetchStatus, Math.max(3, seconds) * 1000)
}

const fetchStatus = async () => {
  try {
    const response = await axios.get(route('exam.waiting-room.status'))
    currentStatus.value = response.data || {}
    if (currentStatus.value.can_start) {
      window.location.href = route('exam.main')
      return
    }
    schedulePoll(currentStatus.value.poll_after_seconds || 15)
  } catch (error) {
    if (error.response?.status === 401) {
      window.location.href = '/'
      return
    }
    schedulePoll(15)
  }
}

onMounted(() => {
  if (currentStatus.value?.can_start) {
    window.location.href = route('exam.main')
    return
  }
  schedulePoll(currentStatus.value?.poll_after_seconds || 15)
})

onBeforeUnmount(clearPoll)
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

          <!-- Exam Date/Time -->
          <div class="bg-gray-50 rounded-lg p-6 mb-8">
            <div class="text-center">
              <div class="text-sm text-gray-600 mb-2">Exam Date & Time</div>
              <div class="text-lg font-bold text-gray-800">
                {{ scheduledDisplay }}
              </div>
              <div class="text-sm text-blue-600 mt-3">
                Please wait. The exam will open automatically.
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
