<script setup>
import AuthoringGroupLayout from '@/Layouts/AuthoringGroupLayout.vue'
import {SearchIcon, DownloadIcon} from '@heroicons/vue/outline'
import {reactive, ref, toRefs, onMounted} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import moment from "moment";

const props = defineProps({
  payload: {
    type: Object,
  },
  takerCount: {
    type: Number,
  },
  deliveryCount: {
    type: Number,
  },
  group: {
    type: Object,
  },
  deliveries: {
    type: Object,
  }
})

const {payload, group, deliveries} = toRefs(props)

const filters = reactive({
  query: null
})

const search = () => {
  Inertia.reload({
    data: {
      page: 1,
      ...filters,
    },
  })
}

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
})

// console.log(payload.value.data, deliveries.value)

const getSummary = (attempts) => {
  if (attempts.length >= 1) {
    let totalScore = 0;
    attempts.forEach((attempt, index) => totalScore = totalScore + attempt.score)
    return totalScore / attempts.length;
  }

  return '-';
}

const getScore = (delivery, attempts) => {
  let attempt = Object.values(attempts).find((obj) => obj.delivery.hash === delivery.hash);
  return (attempt !== undefined) ? attempt.score : '-';
}
</script>

<template>
<AuthoringGroupLayout :group="group" :taker-count="takerCount" :delivery-count="deliveryCount">
  <template #widget>
    <div class="flex justify-end">
      <a
        :href="route('back-office.group.result-pdf', {group_hash: group.hash})"
        target="_blank"
        class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
        <DownloadIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
      </a>
    </div>
  </template>
  <template #default>
    <div class="flex flex-row gap-4 mt-4">
      <input id="search-name-or-email" v-model="filters.query" autocomplete="name-or-email"
             class="focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
             name="query"
             placeholder="Search Name or Email"
             type="text">
      <button
        class="inline-flex justify-center items-center px-4 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        type="button"
        @click="search">
        <SearchIcon aria-hidden="true" class="-ml-1 mr-2 h-5 w-5"/>
        Search
      </button>
    </div>
    <div class="mt-8 flex flex-col">
      <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
          <div class="shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="divide-y divide-gray-300">
              <thead class="bg-gray-50">
              <tr>
                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6" scope="col">NO</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">TAKER</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">SUMMARY</th>
                <th class="px-3 py-3.5 text-center text-xs font-normal text-gray-900" scope="col" v-for="delivery in deliveries">
                  <span class="whitespace-nowrap">{{ delivery.name ?? '-' }} ({{ delivery.exam.name }})</span><br>
                  <span class="text-gray-400">{{ moment(delivery.scheduled_at).format('DD MMM YYYY') }}</span>
                </th>
                <th class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900" scope="col">SUMMARY</th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="(taker, index) in payload.data" :key="`taker-${index}`">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                  {{ payload.from + index }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  <span>{{ taker.name }}</span><br>
                  <span class="text-xs text-gray-400">{{ taker.email }}</span>
                </td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-center text-sm font-medium sm:pr-6">
                  {{ getSummary(taker.attempts) }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center" v-for="delivery in deliveries">
                  {{ getScore(delivery, taker.attempts) }}
                </td>
                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-center text-sm font-medium sm:pr-6">
                  {{ getSummary(taker.attempts) }}
                </td>
              </tr>
              <tr v-if="filters.query !== '' && payload.data.length === 0">
                <td :colspan="3 + deliveries.length">
                  <div class="text-center w-full my-4">Data Not Found.</div>
                </td>
              </tr>
              <tr v-else-if="payload.data.length === 0">
                <td :colspan="3 + deliveries.length">
                  <div class="text-center w-full my-4">Empty, please create Group.</div>
                </td>
              </tr>
              </tbody>
            </table>
            <pagination :pagination-data="payload"/>
          </div>
        </div>
      </div>
    </div>
  </template>
</AuthoringGroupLayout>
</template>

<style scoped>

</style>
