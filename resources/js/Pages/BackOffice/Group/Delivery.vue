<script setup>
import AuthoringGroupLayout from '@/Layouts/AuthoringGroupLayout.vue'
import {SearchIcon, PencilAltIcon, XIcon} from '@heroicons/vue/outline'
import {reactive, toRefs, onMounted, computed, ref, watch} from 'vue'
import {Inertia} from "@inertiajs/inertia";
import Pagination from '@/Components/Pagination.vue'
import urlParser from "@/Libs/urlParser";
import moment from 'moment'
import Select from "@/Components/Select";
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import {useForm} from "@inertiajs/inertia-vue3";
import {Link} from "@inertiajs/inertia-vue3";
import route from "@/Libs/ziggy";

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
    type: Object
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

const statusColors = (status) => {
  const map = {
    'Scheduled': {
      bg: 'bg-purple-100',
      text: 'text-purple-800'
    },
    'On Progress': {
      bg: 'bg-blue-100',
      text: 'text-blue-800'
    },
    'Not Started': {
      bg: 'bg-red-100',
      text: 'text-red-800'
    },
    'Scoring': {
      bg: 'bg-yellow-100',
      text: 'text-yellow-800'
    },
    'Finished': {
      bg: 'bg-green-100',
      text: 'text-green-800'
    }
  }
  return map[status] ?? {
    bg: 'bg-gray-100',
    text: 'text-gray-800'
  }
}

onMounted(() => {
  const urlParams = urlParser()
  filters.query = urlParams.query
})

const showAddModal = ref(false)
const selectedTaker = ref(null)
watch(selectedTaker, (taker, prevTaker) => {
  showAddModal.value = taker !== null
});

const selectedOptions = computed(() => {
  let data = [];
  deliveries.value.forEach((delivery) => {
    data.push({
      text: delivery.name ?? '-',
      value: delivery.hash
    })
  })
  return data;
});

const formAdd = useForm({
  hash: null
})

const resetAddAction = () => {
  showAddModal.value = false
  selectedTaker.value = null
}

const confirmAddAction = () => {
  formAdd.hash = selectedTaker.value.value;
  formAdd.post(route('back-office.group.add-delivery', {group_hash: group.value.hash}), {
    onSuccess: () => resetAddAction()
  })
}

</script>

<template>
  <AuthoringGroupLayout :group="group" :taker-count="takerCount" :delivery-count="deliveryCount">
    <template #widget>
      <Select :options="selectedOptions" title="Attach a delivery to this group .." v-model="selectedTaker" search-placeholder="Search Delivery" />
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
            <div class="overflow-x-scroll shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
              <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                <tr>
                  <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6" scope="col">NO</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">NAME</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col" colspan="2">SCHEDULE</th>
                  <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900" scope="col">STATUS</th>
                  <th class="relative py-3.5 pl-3 pr-4 sm:pr-6" scope="col">
                    <span class="sr-only">Edit</span>
                  </th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                <tr v-for="(delivery, index) in payload.data" :key="`delivery-${index}`">
                  <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                    {{ payload.from + index }}
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <div>
                      <span v-if="delivery.status === 'Scheduled' && delivery.automatic_start" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-red-800 bg-red-200 mr-2">Strict</span>
                      {{ delivery.name ?? '-' }}
                    </div>
                    <span class="text-xs text-gray-400">{{ delivery.display_name }}</span>
                  </td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 uppercase">{{ moment(delivery.scheduled_at).format('DD MMM YYYY / HH:mm') }}</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ delivery.duration }} min</td>
                  <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                    <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusColors(delivery.status).bg, statusColors(delivery.status).text]">
                      {{ delivery.status }}
                    </span>
                  </td>
                  <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                    <Link :href="route('back-office.delivery.scoring', {delivery_hash: delivery.hash, name: 'back-office.group.delivery', params: 'page,group_hash,query', values: `${payload.current_page},${group.hash},${filters.query}`})" class="text-black text-blue-600 hover:text-blue-800">
                      <PencilAltIcon aria-hidden="true" class="-ml-1 h-5 w-5"/>
                    </Link>
                  </td>
                </tr>
                <tr v-if="filters.query !== '' && payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Data Not Found.</div>
                  </td>
                </tr>
                <tr v-else-if="payload.data.length === 0">
                  <td colspan="5">
                    <div class="text-center w-full my-4">Empty, please add Delivery.</div>
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

  <JetConfirmationModal :show="showAddModal" items="center">
    <template #title>
      Add a Delivery
    </template>

    <template #content>
      Are you sure to attach <strong>{{ selectedTaker?.text }}</strong> to this Group?
    </template>

    <template #footer>
      <div class="flex justify-end">
        <button
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          type="button"
          @click="resetAddAction">
          Cancel
        </button>
        <button
          class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          type="submit"
          :disabled="formAdd.processing"
          @click="confirmAddAction">
          Yes, add delivery.
        </button>
      </div>
    </template>
  </JetConfirmationModal>
</template>

<style scoped>

</style>
