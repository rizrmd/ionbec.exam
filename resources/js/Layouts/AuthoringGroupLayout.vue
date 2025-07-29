<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import {ChevronLeftIcon, TrashIcon, PencilAltIcon, ClipboardListIcon, PrinterIcon, UsersIcon, ChartBarIcon} from '@heroicons/vue/outline'
import {toRefs} from "vue";
import route from "@/Libs/ziggy";
import {Link, useForm} from '@inertiajs/inertia-vue3'
import {ref, onMounted} from "vue";
import JetDialogModal from '@/Jetstream/DialogModal'
import JetConfirmationModal from '@/Jetstream/ConfirmationModal'
import JetValidationErrors from '@/Jetstream/ValidationErrors'
import JetActionMessage from '@/Jetstream/ActionMessage'
import JetBanner from '@/Jetstream/Banner'
import urlParser from "@/Libs/urlParser";
import generatePrevUrl from "@/Libs/generatePrevUrl";
import CardCounter from "@/Components/CardCounter";
import Datepicker from '@/Components/Datepicker.vue'
import moment from "moment";

const props = defineProps({
  takerCount: {
    type: Number,
  },
  deliveryCount: {
    type: Number,
  },
  group: {
    type: Object,
  }
})

const {group} = toRefs(props);

const generateRouteName = (name) => "back-office.group." + name;

const form = useForm({
  name: null,
  description: null,
  code: null,
  last_taker_code: null,
  closed_at: null
})

const isModalFormOpen = ref(false)
const openModalForm = () => {
  isModalFormOpen.value = true
  form.name = group.value.name
  form.description = group.value.description
  form.code = group.value.code
  form.last_taker_code = group.value.last_taker_code
  form.closed_at = group.value.closed_at
    ? moment(group.value.closed_at, "YYYY-MM-DD hh:mm").format('YYYY-M-D')
    : moment().format('YYYY-M-D')
}

const closeModalForm = () => {
  form.clearErrors()
  isModalFormOpen.value = false
}

const editAction = () => {
  form.put(route('back-office.group.update', {group_hash: group.value.hash}), {
    onSuccess: closeModalForm
  })
}

const showModalDelete = ref(false)
const openModalDelete = () => showModalDelete.value = true;
const cancelDeleteAction = () => showModalDelete.value = false
const confirmDeleteAction = () => form.delete(route('back-office.group.destroy', {group_hash: group.value.hash}))

const prevUrl = ref(null);
onMounted(() => {
  const urlParams = urlParser()
  prevUrl.value = generatePrevUrl(urlParams.name, urlParams.params, urlParams.values)
})
</script>

<template>
  <DashboardLayout title="Authoring Group">
    <template #default>
      <JetActionMessage :on="form.recentlySuccessful" class="my-2">
        <JetBanner/>
      </JetActionMessage>
      <div class="flex justify-between sm:px-6 lg:px-0 mb-5">
        <div class="flex">
          <div class="flex justify-center items-center mr-4">
            <Link :href="prevUrl ?? route('back-office.group.index')" class="hover:bg-gray-200 px-3 py-2 rounded-sm">
              <ChevronLeftIcon aria-hidden="true" class="h-5 w-5"/>
            </Link>
          </div>
          <div>
            <h1 class="text-xl font-semibold text-gray-900">{{ group.name }}</h1>
            <h2 class="text-base text-gray-700">{{ group.description ?? '-' }}</h2>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <a
            :href="route('back-office.group.taker-pdf', {group_hash: group.hash})"
            target="_blank"
            class="inline-flex items-center px-3 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            <PrinterIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
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
            @click="openModalDelete">
            <TrashIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
          </button>
        </div>
      </div>
      <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4 mb-5">
        <card-counter title="Total Takers" :count="takerCount" :icon="UsersIcon"/>
        <card-counter title="Total Deliveries" :count="deliveryCount" :icon="ClipboardListIcon"/>
        <card-counter title="Results" :count="takerCount" :icon="ChartBarIcon"/>
      </div>
      <div class="flex flex-row items-center">
        <div class="basis-6/12">
          <nav class="flex space-x-4" aria-label="Tabs">
            <Link :href="route(generateRouteName('taker'), {group_hash: group.hash})" :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap', (route().current() === generateRouteName('taker')) ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Candidates</Link>
            <Link :href="route(generateRouteName('delivery'), {group_hash: group.hash})" :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap', (route().current() === generateRouteName('delivery')) ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Deliveries</Link>
            <Link :href="route(generateRouteName('result'), {group_hash: group.hash})" :class="['py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap', (route().current() === generateRouteName('result')) ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">Results</Link>
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
            <h4>Edit Group</h4>
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
                <input id="name" v-model="form.name"
                       class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                       name="name"
                       type="text"/>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="description">
                Description <small class="text-red-500">*</small>
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
            <textarea id="description" v-model="form.description"
                      class="max-w-lg h-16 block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                      type="text"></textarea>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="code">
                Code <small class="text-red-500">*</small>
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <input id="code" v-model="form.code"
                       class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                       placeholder="e.g. TG01, TSK0323, CBT0423"
                       type="text"/>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2" for="last_taker_code">
                Last Taker Count <small class="text-red-500">*</small>
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <input id="last_taker_code" v-model="form.last_taker_code"
                       class="max-w-lg block w-full shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                       placeholder="e.g. TG01, TSK0323, CBT0423"
                       type="text"/>
              </div>
            </div>
            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
              <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                Open Registration Until
              </label>
              <div class="mt-1 sm:mt-0 sm:col-span-2">
                <div class="max-w-lg block w-full sm:max-w-xs">
                  <Datepicker v-model="form.closed_at" placeholder="Pick a date" :enable-time="false" position-y="bottom"/>
                </div>
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

      <JetConfirmationModal :show="showModalDelete" items="center">
        <template #title>
          Delete Group {{ group?.name }}
        </template>

        <template #content>
          Group cannot be recovered after deletion.
        </template>

        <template #footer>
          <div class="flex justify-end">
            <button
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
              type="button"
              @click="cancelDeleteAction">Cancel
            </button>
            <button
              class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              type="submit"
              :disabled="form.processing"
              @click="confirmDeleteAction">
              Yes, Delete Group
            </button>
          </div>
        </template>
      </JetConfirmationModal>

    </template>
  </DashboardLayout>
</template>
