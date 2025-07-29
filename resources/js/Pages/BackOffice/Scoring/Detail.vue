<script setup>
import {SearchIcon, DownloadIcon, PencilAltIcon, ChevronLeftIcon, CheckIcon, ExclamationIcon} from '@heroicons/vue/outline'
import {reactive, ref, toRefs, onMounted, computed} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import {Link} from "@inertiajs/inertia-vue3";
import moment from 'moment'
import DashboardLayout from "@/Layouts/DashboardLayout";
import CardCounter from "@/Components/CardCounter";
import generatePrevUrl from "@/Libs/generatePrevUrl";
import route from "@/Libs/ziggy";

const props = defineProps({
  payload: {
    type: Object,
  },
  takerCount: {
    type: Number,
  },
  scoringCount: {
    type: Number,
  },
  questionCount: {
    type: Number,
  },
  delivery: {
    type: Object,
  },
})

const {payload, group, takers, delivery} = toRefs(props)

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

const dataScoring = computed(() => payload.value)
const prevUrl = ref(null);
onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
  prevUrl.value = generatePrevUrl(urlParams.name, urlParams.params, urlParams.values)
})

window.Echo.private(`scoring.${delivery.value.hash}`)
  .listen('ScoringEvent', (e) => {
    const attempt = e.attempt;
    let index = dataScoring.value.data.findIndex((data) => data.hash === attempt.hash)
    if (index >= 0) {
      const newDataScoring = dataScoring.value;
      newDataScoring.data[index].progress = attempt.progress;
      newDataScoring.data[index].score = attempt.score;
      dataScoring.value = newDataScoring
    }
  });
</script>

<template>
  <DashboardLayout title="Scoring Detail">
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Scoring Detail</h1>
      </div>
    </template>
    <div class="flex justify-between items-center sm:px-6 lg:px-0 mb-5">
      <div class="flex justify-center items-center gap-4">
        <Link :href="prevUrl ?? route('back-office.scoring.index')" class="hover:bg-gray-200 px-3 py-2 rounded-sm">
          <ChevronLeftIcon aria-hidden="true" class="h-5 w-5"/>
        </Link>
        <div>
          <h1 class="text-xl font-semibold text-gray-900">{{ delivery.name }}</h1>
          <h2 class="text-base text-gray-700">{{ delivery.exam.name ?? '' }} - {{ delivery.exam.code ?? '' }}</h2>
        </div>
      </div>
      <div v-if="delivery.is_interview">
        <a :href="route('back-office.interview.live-question', {delivery_hash: delivery.hash, reset: true})" target="_blank" class="mr-4 px-3 py-1 border border-transparent shadow-sm text-sm rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
          Reset Live Session
        </a>
        <a :href="route('back-office.interview.live-question', {delivery_hash: delivery.hash})" target="_blank" class="px-3 py-1 border border-transparent shadow-sm text-sm rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
          Open Live Interview
        </a>
      </div>
    </div>
    <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
      <card-counter key="takers" title="Scoring" :count="scoringCount"/>
      <card-counter key="deliveries" title="Takers" :count="takerCount"/>
      <card-counter key="results" title="Question" :count="questionCount"/>
    </div>

    <div class="flex flex-row gap-4 mt-4">
      <input id="search-query" v-model="filters.query" autocomplete="query"
             class="flex-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
             placeholder="Search Code or Email"
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
          <div class="overflow-x-scroll shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
              <thead class="bg-gray-50">
              <tr>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NO</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">CODE</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center" scope="col">PROGRESS</th>
                <th v-if="!delivery.is_interview" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">ATTEMPTED AT</th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">
                  <span v-if="delivery.is_interview">STATUS INTERVIEW</span>
                  <span v-else>STATUS SCORING</span>
                </th>
                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 text-center" scope="col">SCORE</th>
                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                  <span class="sr-only">download</span>
                </th>
              </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="(attempt, index) in dataScoring.data" :key="`attempt-${index}`">
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                  {{ dataScoring.from + index }}
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ attempt.taker_code }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                  <div class="h-1.5 rounded-lg w-full bg-gray-200 overflow-hidden relative">
                    <div class="h-full bg-primary-600" :style="`width: ${attempt.progress}%`"></div>
                  </div>
                  <span>{{ attempt.progress }}%</span>
                </td>
                <td v-if="!delivery.is_interview" class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ moment(attempt.created_at).format('DD MMM YYYY') }} at {{ moment(attempt.created_at).format('H:mm') }}</td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <span v-if="attempt.finish_scoring" class="py-1.5 px-2 rounded-full text-green-800 bg-green-50 flex items-center gap-2 w-max">
                      <CheckIcon class="h-5 w-5 text-green-400" /> Finished
                    </span>
                  <span v-else class="py-1.5 px-2 rounded-full text-yellow-800 bg-yellow-50 flex items-center gap-2 w-max">
                      <ExclamationIcon class="h-5 w-5 text-yellow-400" /> Not finished yet
                    </span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">{{ attempt.score }}</td>
                <td class="flex gap-4 justify-end items-center py-4" style="max-width: 50px">
                  <a :href="route('back-office.scoring.pdf', {delivery_hash: delivery.hash, attempt_hash: attempt.hash})" target="_blank" class="text-black hover:text-indigo-500 hover:scale-125">
                    <DownloadIcon aria-hidden="true" class="-ml-1 h-5 w-5"/>
                  </a>
                  <a :href="route('back-office.scoring.exam', {delivery_hash: delivery.hash, attempt_hash: attempt.hash})" target="_blank" class="text-black hover:text-indigo-500 hover:scale-125">
                    <PencilAltIcon aria-hidden="true" class="-ml-1 h-5 w-5"/>
                  </a>
                </td>
              </tr>
              <tr v-if="filters.query !== '' && dataScoring.data.length === 0">
                <td colspan="6">
                  <div class="text-center w-full my-4">Data Not Found.</div>
                </td>
              </tr>
              <tr v-else-if="dataScoring.data.length === 0">
                <td colspan="6">
                  <div class="text-center w-full my-4">Empty.</div>
                </td>
              </tr>
              </tbody>
            </table>
            <pagination :pagination-data="dataScoring"/>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>

</style>
