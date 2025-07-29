<script setup>
import AuthoringDeliveryLayout from '@/Layouts/AuthoringDeliveryLayout.vue'
import {ArrowUpIcon, RefreshIcon, SearchIcon} from '@heroicons/vue/outline'
import {onMounted, reactive, toRefs} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import {Link, useForm} from "@inertiajs/inertia-vue3";
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetBanner from '@/Jetstream/Banner'
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
  tests: {
    type: Object,
  },
  groups: {
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

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
})

const form = useForm({
  hash: null,
})

const generateAllToken = () => {
  form.hash = null
  form.post(route('back-office.delivery.generate-token', {delivery_hash: delivery.value.hash}))
}

const refreshToken = (hash) => {
  form.hash = hash
  form.post(route('back-office.delivery.generate-token', {delivery_hash: delivery.value.hash}))
}

const attemptInterview = (delivery_hash, taker_hash) => {
  axios.post(route('back-office.delivery.attempt-interview', {
    delivery_hash: delivery_hash,
    taker_hash: taker_hash
  })).then(response => {
    window.open(route('back-office.delivery.scoring-detail', {
      delivery_hash: delivery_hash,
      attempt_hash: response.data.attempt_hash
    }))
    window.location.reload()
  })
}
</script>

<template>
  <AuthoringDeliveryLayout :delivery="delivery" :taker-count="takerCount" :scoring-count="scoringCount"
                           :question-count="questionCount" :tests="tests" :groups="groups">
    <template #widget>
      <div class="flex gap-2 justify-end">
        <button @click="generateAllToken()"
                class="px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
          <span v-if="delivery.is_interview">Attempt All</span>
          <span v-else>Fresh All Tokens</span>
        </button>
        <Link v-if="delivery.group.hash !== undefined"
              :href="route('back-office.group.taker', {group_hash: delivery.group.hash, name: 'back-office.delivery.taker', params: 'delivery_hash', values: delivery.hash})"
              class="flex justify-center items-center px-2.5 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
          Show Assigned Group
          <ArrowUpIcon aria-hidden="true" class="ml-1.5 h-4 w-4 rotate-45"/>
        </Link>
      </div>
    </template>
    <template #default>
      <JetActionMessage :on="form.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="flex flex-row gap-4 mt-4">
        <input id="search-name-or-email" v-model="filters.query" autocomplete="name-or-email"
               class="flex-1 focus:ring-primary-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
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
            <div class="overflow-x-scroll shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NO</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">CODE</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NAME</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">EMAIL</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col"
                      v-if="delivery.is_interview">INTERVIEW
                  </th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col"
                      v-if="!delivery.is_interview">TOKEN
                  </th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col"
                      v-if="!delivery.is_interview">STATUS
                  </th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col" v-if="!delivery.is_interview">
                    <span class="sr-only">refresh</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(taker, index) in payload.data" :key="`taker-${index}`">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ payload.from + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ taker.taker_code }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ taker.name }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ taker.email }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <a href="#"
                       class="text-sm text-blue-500"
                       @click="attemptInterview(delivery.hash, taker.hash)"
                       v-if="taker.attempt_hash == null">
                      Attempt Now
                    </a>
                    <a href="#"
                       class="text-sm text-green-500"
                       @click="attemptInterview(delivery.hash, taker.hash)"
                       v-else>
                      Attempted
                    </a>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" v-if="!delivery.is_interview">
                    {{ taker.deliveries.length >= 1 ? taker.deliveries[taker.deliveries.length - 1].pivot.token : "" }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" v-if="!delivery.is_interview">{{
                      taker.deliveries.length >= 1 && taker.deliveries[taker.deliveries.length - 1].pivot.is_login ? 'Logged in' : '-'
                    }}
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6"
                      v-if="!delivery.is_interview">
                    <button class="text-black hover:text-blue-500 hover:scale-125" @click="refreshToken(taker.hash)">
                      <RefreshIcon aria-hidden="true" class="-ml-1 h-5 w-5"/>
                    </button>
                  </td>
                </tr>
                <tr v-if="filters.query !== '' && payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Empty.</div>
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
  </AuthoringDeliveryLayout>
</template>

<style scoped>

</style>
