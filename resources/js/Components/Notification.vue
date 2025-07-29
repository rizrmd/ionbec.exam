<template>
  <!-- Global notification live region, render this permanently at the end of the document -->
  <div aria-live="assertive" class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start" style="z-index: 9999">
    <div class="w-full flex flex-col items-center space-y-4 sm:items-end fixed bottom-16 right-16">
      <!-- Notification panel, dynamically insert this into the live region when it needs to be displayed -->
      <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0" v-for="notif in notificationData">
        <div v-if="notif.active" class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
          <div class="p-4">
            <div class="flex items-start">
              <div class="flex-shrink-0" v-if="notif.type === 'success' || notif.type === 'error' || notif.type === 'danger' || notif.type === 'warning'">
                <CheckCircleIcon class="h-6 w-6 text-green-400" aria-hidden="true" v-if="notif.type === 'success'"/>
                <XCircleIcon class="h-6 w-6 text-red-400" aria-hidden="true" v-else-if="notif.type === 'error' || notif.type === 'danger'"/>
                <ExclamationIcon class="h-6 w-6 text-yellow-400" aria-hidden="true" v-else-if="notif.type === 'warning'"/>
              </div>
              <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-gray-900">{{ notif.title }}</p>
                <p class="mt-1 text-sm text-gray-500" v-if="notif.message !== ''">{{ notif.message }}</p>
              </div>
              <div class="ml-4 flex-shrink-0 flex">
                <button type="button" @click="removeNotif(notif.id)" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                  <span class="sr-only">Close</span>
                  <XIcon class="h-5 w-5" aria-hidden="true" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import {XIcon, CheckCircleIcon, XCircleIcon, ExclamationIcon} from '@heroicons/vue/outline'
import {ref, watch, computed} from "vue";
import {notification} from "@/Store/notification";
import {usePage} from "@inertiajs/inertia-vue3";

const props = defineProps({
  defaultTimeout: {
    type: Number,
    default: 5,
  },
})

const messageStyle = computed(() => usePage().props.value.jetstream.flash?.bannerStyle || 'success');
const message = computed(() => usePage().props.value.jetstream.flash?.banner || '');
const errors = computed(() => usePage().props.value.errors);

const notificationData = ref([])

notification.data.forEach((data) => {
  notificationData.value.push({
    active: true,
    ...data
  })
})

watch(errors, function() {
  if (Object.keys(errors.value).length > 0) {
    Object.keys(errors.value).forEach((key) => {
      notification.add('warning', 'Whoops! Something went wrong.', errors.value[key], props.defaultTimeout ?? 5)
    })
  }
})

watch(message, function() {
  if (message.value !== '') {
    let status = messageStyle.value.charAt(0).toUpperCase() + messageStyle.value.slice(1);
    if (status === 'danger' || status === 'error') {
      status = 'Failed'
    }
    notification.add(messageStyle, status, message, props.defaultTimeout ?? 5)
  }
})

watch(notification.data, function (value, prevVal) {
  // console.log('new Val:', value)
  let alive = [];
  value.forEach((data) => {
    const n = notificationData.value.find((notif) => notif.id === data.id);
    if (n === undefined) {
      const index = notificationData.value.length
      notificationData.value.push({
        active: false,
        ...data
      })
      setTimeout(() => {
        notificationData.value[index].active = true;
      }, 100)
    }
    alive.push(data.id)
  })

  notificationData.value.forEach((data, index) => {
    if (!alive.includes(data.id)) {
      notificationData.value[index].active = false
    }
  })

  if (value.length === 0) {
    setTimeout(() => {
      notificationData.value = []
    }, 200)
  }
})

const removeNotif = (id) => {
  notification.remove(id)
}
</script>

<style scoped>

</style>
