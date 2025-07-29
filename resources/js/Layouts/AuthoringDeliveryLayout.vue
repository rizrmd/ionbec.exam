<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {ChevronLeftIcon, TrashIcon, PencilAltIcon, ChartBarIcon, UsersIcon, ClipboardListIcon, ExternalLinkIcon} from '@heroicons/vue/outline'
import CardCounter from "@/Components/CardCounter";
import {toRefs, computed, onMounted} from "vue";
import route from "@/Libs/ziggy";
import {Link, useForm} from '@inertiajs/inertia-vue3'
import {ref} from "vue";
import JetDialogModal from '@/Jetstream/DialogModal'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetBanner from '@/Jetstream/Banner'
import Select from "@/Components/Select"
import Datepicker from '@/Components/Datepicker.vue'
import moment from "moment";
import Message from "@/Components/Message.vue"
import generatePrevUrl from "@/Libs/generatePrevUrl";
import urlParser from "@/Libs/urlParser";

const props = defineProps({
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

const statusColors = (status) => {
  const map = {
    'Scheduled': {
      bg: 'bg-purple-100',
      text: 'text-purple-800',
      border: 'border-purple-800'
    },
    'On Progress': {
      bg: 'bg-blue-100',
      text: 'text-blue-800',
      border: 'border-blue-800'
    },
    'Not Started': {
      bg: 'bg-red-100',
      text: 'text-red-800',
      border: 'border-red-800'
    },
    'Scoring': {
      bg: 'bg-yellow-100',
      text: 'text-yellow-800',
      border: 'border-yellow-800'
    },
    'Finished': {
      bg: 'bg-green-100',
      text: 'text-green-800',
      border: 'border-green-800'
    }
  }
  return map[status] ?? {
    bg: 'bg-gray-100',
    text: 'text-gray-800'
  }
}

const {delivery, tests, groups} = toRefs(props);

const selOpTest = computed(() => {
  let data = [];
  tests.value.forEach((exam) => {
    data.push({
      text: exam.name,
      value: exam.hash
    })
  })
  return data;
});

const selOpGroup = computed(() => {
  let data = [];
  groups.value.forEach((group) => {
    data.push({
      text: group.name,
      value: group.hash
    })
  })
  return data;
});

const generateRouteName = (name) => "back-office.delivery." + name;

const form = useForm({
  exam: null,
  exam_hash: null,
  group: null,
  group_hash: null,
  name: null,
  display_name: null,
  scheduled_at: null,
  duration: null,
  automatic_start: false,
})

const prevUrl = ref(null);
onMounted(() => {
  const urlParams = urlParser()
  prevUrl.value = generatePrevUrl(urlParams.name, urlParams.params, urlParams.values)
})

const isModalFormOpen = ref(false)
const openModalForm = () => {
  isModalFormOpen.value = true
  form.name = delivery.value.name
  form.display_name = delivery.value.display_name
  form.exam = selOpTest.value.find((test) => test.value === delivery.value.exam.hash)
  form.group = selOpGroup.value.find((test) => test.value === delivery.value.group.hash)
  form.scheduled_at = moment(delivery.value.scheduled_at).format('YYYY-M-D HH:mm')
  form.duration = delivery.value.duration
  form.automatic_start = delivery.value.automatic_start
  form.exam_hash = delivery.value.exam.hash
  form.group_hash = delivery.value.group.hash
}

const closeModalForm = () => {
  form.clearErrors()
  isModalFormOpen.value = false
}

const editAction = () => {
  form.exam_hash = (form.exam) ? form.exam.value : null
  form.group_hash = (form.group) ? form.group.value : null
  form.put(route('back-office.delivery.update', {delivery_hash: delivery.value.hash}), {
    onSuccess: closeModalForm
  })
}

const modaldelete = ref(false)
const confirmDeleteAction = () => form.delete(route('back-office.delivery.destroy', {delivery_hash: delivery.value.hash}))

defineExpose({
  openModalForm,
});

const modalFinish = ref(false)
const setToFinish = () => form.post(route('back-office.delivery.finish', {delivery_hash: delivery.value.hash}), {
  onSuccess: () => modalFinish.value = false
})

const modalScheduleNow = ref(false)
const generateTokenToScheduleOrStart = () => {
  useForm({hash: null}).post(route('back-office.delivery.generate-token', {delivery_hash: delivery.value.hash}), {
    onSuccess: () => modalScheduleNow.value = false
  })
}
</script>

<template>
  <DashboardLayout title="Authoring Delivery">
    <template #header>
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-semibold text-gray-900">Authoring Delivery</h1>
        <div class="flex gap-4">
          <button v-if="!delivery.is_finished && delivery.status === 'Scoring'" class="px-3 py-1 border border-transparent shadow-sm text-sm rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" @click="modalFinish = true">Set as Completed</button>
          <button v-if="!delivery.is_finished && delivery.status === 'Created'" class="px-3 py-1 border border-transparent shadow-sm text-sm rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500" @click="modalScheduleNow = true">Schedule Now</button>
          <button v-if="!delivery.is_finished && delivery.status === 'Not Started'" class="px-3 py-1 border border-transparent shadow-sm text-sm rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" @click="modalScheduleNow = true">Starts Now</button>
          <div :class="['inline-flex items-center px-4 py-2 rounded border text-sm font-medium', statusColors(delivery.status).bg, statusColors(delivery.status).text, statusColors(delivery.status).border]">
            Status is&nbsp;<span class="font-bold">{{ delivery.status }}</span>
          </div>
        </div>
      </div>
    </template>
    <template #default>
      <JetActionMessage :on="form.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="flex justify-between sm:px-6 lg:px-0 mb-5">
        <div class="flex">
          <div class="flex justify-center items-center mr-4">
            <Link :href="prevUrl ?? route('back-office.delivery.index')" class="hover:bg-gray-200 px-3 py-2 rounded-sm">
              <ChevronLeftIcon aria-hidden="true" class="h-5 w-5"/>
            </Link>
          </div>
          <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ delivery.name }}</h1>
            <h2 class="text-base text-gray-700">{{ delivery.exam.name ?? '' }} - {{ delivery.exam.code ?? '' }}</h2>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <a
            :href="route('back-office.delivery.goto', {delivery_hash: delivery.hash})"
            target="_blank"
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          >
            <ExternalLinkIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
          </a>
          <button
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900"
            type="button"
            @click="openModalForm">
            <PencilAltIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
          </button>
          <button
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
            type="button"
            @click="modaldelete = true">
            <TrashIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
          </button>
        </div>
      </div>
      <div class="mb-4">
        <Message :message="'Exam scheduled at ' + moment(delivery.scheduled_at).format('DD MMM YYYY HH:mm')"/>
      </div>
      <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
        <card-counter title="Scored" :count="scoringCount" :icon="ChartBarIcon"/>
        <card-counter title="Participants" :count="takerCount" :icon="UsersIcon"/>
        <card-counter title="Questions" :count="questionCount" :icon="ClipboardListIcon"/>
      </div>
      <div class="flex flex-row items-center">
        <div class="basis-6/12">
          <nav class="flex space-x-4" aria-label="Tabs">
            <Link :href="route(generateRouteName('scoring'), {delivery_hash: delivery.hash})" :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap', (route().current() === generateRouteName('scoring')) ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Scoring</Link>
            <Link :href="route(generateRouteName('taker'), {delivery_hash: delivery.hash})" :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap', (route().current() === generateRouteName('taker')) ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Candidates</Link>
            <Link :href="route(generateRouteName('question'), {delivery_hash: delivery.hash})" :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap', (route().current() === generateRouteName('question')) ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Questions</Link>
          </nav>
        </div>
        <div class="basis-6/12">
          <slot name="widget"/>
        </div>
      </div>
      <div>
        <slot/>
      </div>

      <JetDialogModal :show="isModalFormOpen" @close="closeModalForm" items="center">
        <template #title>
          <div class="flex justify-between">
            <h4>Edit Delivery</h4>
          </div>
        </template>

        <template #content>
          <JetValidationErrors class="my-4"/>
          <div class="mt-6 sm:mt-5 space-y-6 sm:space-y-5">
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="name">
                Name <small class="text-red-500">*</small>
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <input id="name" v-model="form.name" autofocus
                       class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                       type="text"/>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="display_name">
                Display Name <small class="text-red-500">*</small>
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <input id="display_name" v-model="form.display_name" autofocus
                       class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                       type="text"/>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="test">
                Test <small class="text-red-500">*</small>
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <div class="max-w-lg block w-full sm:max-w-xs">
                  <Select :options="selOpTest" title="Select Test" v-model="form.exam" search-placeholder="Search Test"></Select>
                </div>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="groouo">
                Group <small class="text-red-500">*</small>
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <div class="max-w-lg block w-full sm:max-w-xs">
                  <Select :options="selOpGroup" title="Select Group" v-model="form.group" search-placeholder="Search Group" />
                </div>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                Schedule At
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <div class="max-w-lg block w-full sm:max-w-xs">
                  <Datepicker v-model="form.scheduled_at" placeholder="Pick a date" :enable-time="true" position-y="bottom"/>
                </div>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="duration">
                Duration
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <input id="duration" v-model="form.duration" autofocus
                       class="w-36 shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                       type="number"/>
                <span class="ml-2 text-gray-500">minute</span>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
              <label class="block text-sm font-medium text-gray-700">
                -
              </label>
              <div class="mt-0.5 sm:col-span-2 flex items-center gap-2">
                <input id="automatic-start" type="checkbox" class="form-checkbox rounded text-primary-500 focus:ring-primary-500" checked v-model="form.automatic_start">
                <label class="flex flex-col justify-start" for="automatic-start">
                  <span>Strict to schedule</span>
                  <span class="text-xs">(a waiting page will be shown if checked)</span>
                </label>
              </div>
            </div>
          </div>
        </template>

        <template #footer>
          <div class="flex justify-end">
            <button
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              type="button"
              @click="closeModalForm">Cancel
            </button>
            <button
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              type="submit"
              :disabled="form.processing"
              @click="editAction">
              Save
            </button>
          </div>
        </template>
      </JetDialogModal>

      <JetConfirmationModal :show="modaldelete" items="center">
        <template #title>
          Delete Delivery {{ delivery?.name }}
        </template>

        <template #content>
          Delivery cannot be recovered after deletion.
        </template>

        <template #footer>
          <div class="flex justify-end">
            <button
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              type="button"
              @click="modaldelete = false">Cancel
            </button>
            <button
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              type="submit"
              @click="confirmDeleteAction">
              Yes, Delete Delivery
            </button>
          </div>
        </template>
      </JetConfirmationModal>

      <JetConfirmationModal :show="modalFinish" items="center">
        <template #title>
          Finish Delivery {{ delivery?.name }}
        </template>

        <template #content>
          Ensure that all scoring already attempted, this will ensure that any scoring actions will be closed.
        </template>

        <template #footer>
          <div class="flex justify-end">
            <button
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              type="button"
              @click="modalFinish = false">
              Cancel
            </button>
            <button
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
              type="submit"
              @click="setToFinish">
              Yes, Finish Now
            </button>
          </div>
        </template>
      </JetConfirmationModal>

      <JetConfirmationModal :show="modalScheduleNow" items="center">
        <template #title>
          <span v-if="delivery.is_interview">Enable interviewer to score questions.</span>
          <span v-else>Generate token for all participants now.</span>
        </template>

        <template #content>
          This will schedule or may starts the delivery if within the schedule.
        </template>

        <template #footer>
          <div class="flex justify-end">
            <button
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              type="button"
              @click="modalScheduleNow = false">
              Cancel
            </button>
            <button
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
              type="submit"
              @click="generateTokenToScheduleOrStart">
              <span v-if="delivery.is_interview">Yes, Enable Now</span>
              <span v-else>Yes, Generate Now</span>
            </button>
          </div>
        </template>
      </JetConfirmationModal>

    </template>
  </DashboardLayout>
</template>
