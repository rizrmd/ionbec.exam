<script setup>
import { onMounted, toRefs } from 'vue'
import moment from 'moment'

const props = defineProps({
  delivery: {
    type: Object,
  },
  group: {
    type: Object,
  },
  takers: {
    type: Array,
  },
})

const { delivery, group, takers } = toRefs(props)

onMounted(() => {
  window.print();
})
</script>

<template>
  <div class="max-w-6xl mx-auto p-8 bg-white">
    <!-- Header -->
    <div class="mb-8 text-center border-b-2 border-gray-300 pb-6">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Candidate Token List</h1>
      <h2 class="text-xl text-gray-700 mb-1">{{ delivery.name }}</h2>
      <p class="text-gray-600">Group: {{ group.name }}</p>
      <p class="text-sm text-gray-500">
        Scheduled at: {{ moment(delivery.scheduled_at).format('DD MMMM YYYY, HH:mm') }}
      </p>
    </div>

    <!-- Table -->
    <table class="min-w-full border-collapse border border-gray-300">
      <thead>
        <tr class="bg-gray-100">
          <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-900" style="width: 60px;">NO</th>
          <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-900" style="width: 150px;">CODE</th>
          <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-900">NAME</th>
          <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-900">EMAIL</th>
          <th class="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-900" style="width: 120px;">TOKEN</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(taker, index) in takers" :key="taker.hash" class="hover:bg-gray-50">
          <td class="border border-gray-300 px-4 py-3 text-sm text-gray-900">{{ index + 1 }}</td>
          <td class="border border-gray-300 px-4 py-3 text-sm text-gray-900">{{ taker.taker_code }}</td>
          <td class="border border-gray-300 px-4 py-3 text-sm text-gray-900">{{ taker.name }}</td>
          <td class="border border-gray-300 px-4 py-3 text-sm text-gray-700">{{ taker.email }}</td>
          <td class="border border-gray-300 px-4 py-3 text-sm font-bold text-gray-900">{{ taker.token || '-' }}</td>
        </tr>
        <tr v-if="takers.length === 0">
          <td colspan="5" class="border border-gray-300 px-4 py-8 text-center text-gray-500">
            No candidates found
          </td>
        </tr>
      </tbody>
    </table>

    <!-- Footer -->
    <div class="mt-8 text-center text-sm text-gray-500">
      <p>Total Candidates: {{ takers.length }}</p>
      <p class="mt-2">Generated on: {{ moment().format('DD MMMM YYYY, HH:mm:ss') }}</p>
    </div>
  </div>
</template>

<style scoped>
@media print {
  body {
    print-color-adjust: exact;
    -webkit-print-color-adjust: exact;
  }

  table {
    page-break-inside: auto;
  }

  tr {
    page-break-inside: avoid;
    page-break-after: auto;
  }
}
</style>
