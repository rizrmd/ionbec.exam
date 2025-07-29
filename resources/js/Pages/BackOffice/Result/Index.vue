<template>
  <dashboard-layout title="Result of Groups">
    <template #header>
      <div class="flex justify-between sm:px-6 lg:px-0">
        <h1 class="text-2xl font-semibold text-gray-900">Result of Groups</h1>
        <div>

        </div>
      </div>
    </template>

    <template #default>
      <div class="flex flex-row gap-4">
        <input id="search-name-or-description" v-model="filters.query" autocomplete="name-or-description"
               class="flex-1 mt-1 focus:ring-priamry-500 focus:border-primary-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
               name="query"
               placeholder="Search Name or Description"
               type="text">
        <button
          class="flex-none inline-flex justify-center items-center px-4 py-1.5 mt-1 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
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
                  <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-20" scope="col">NO</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NAME</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">PARTICIPANTS</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Edit</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(group, index) in payload.data" :key="`group-${index}`" class="hover:bg-gray-50">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ payload.from + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ group.name }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ group.takers.length }} Participants</td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 flex justify-end">
                    <Link :href="route('back-office.result.detail', {group_hash: group.hash})" class="text-primary-600 hover:text-primary-900">
                      <ExternalLinkIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                    </Link>
                  </td>
                </tr>
                <tr v-if="(filters.query !== null || filters.role !== null) && payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="5">
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
  </dashboard-layout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {SearchIcon, ExternalLinkIcon} from '@heroicons/vue/outline'
import {reactive, ref, toRefs, onMounted} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import {Link} from "@inertiajs/inertia-vue3";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";

const props = defineProps({
  payload: {
    type: Object,
  },
})

const {payload} = toRefs(props)

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
  if (urlParams.modal_create) openModalForm()
})

</script>

<style scoped>

</style>
