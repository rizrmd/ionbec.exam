<script setup>
import TakerLayout from '@/Layouts/TakerLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import moment from 'moment'
import {ExternalLinkIcon, DownloadIcon} from '@heroicons/vue/outline'
import {useForm} from "@inertiajs/inertia-vue3";
import route from "@/Libs/ziggy";

const props = defineProps({
  payload: {
    type: Object
  },
});

const gotoDelivery = (hash) => {
  const form = useForm({ delivery_hash: hash })
  form.post(route('taker.schedules.login'))
}
</script>

<template>
  <taker-layout title="Schedule">
    <div class="mt-8 flex flex-col">
      <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
          <div class="overflow-x-scroll shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
            <table class="min-w-full divide-y divide-gray-300">
              <tbody class="divide-y divide-gray-200 bg-white">
              <tr v-for="(delivery, index) in payload.data" :key="`delivery-${index}`">
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 sm:pl-6">{{ delivery.display_name ?? '-' }}</td>
                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                  <span v-if="delivery.status.toLowerCase() == 'scheduled'" class="text-green-600">{{ delivery.status }}</span>
                  <span v-if="delivery.status.toLowerCase() == 'on progress'" class="text-red-600">{{ delivery.status }}</span>
                  <span v-if="delivery.status.toLowerCase() == 'scoring'" class="text-blue-600">{{ delivery.status }}</span>
                  <span v-if="delivery.status.toLowerCase() == 'finished'">{{ delivery.status }}</span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">
                  <div>{{ moment(delivery.scheduled_at).format('D MMM YYYY HH:mm') }}</div>
                  <span class="text-xs text-orange-500" v-if="delivery.automatic_start">Strict to schedule.</span>
                  <span class="text-xs text-blue-500" v-else>Restricted by date.</span>
                </td>
                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ delivery.duration }} min</td>
                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-sm sm:pr-6 text-right">
                  <div class="float-right w-5" v-if="delivery.status.toLowerCase() === 'finished'">
                    <a :href="route('taker.schedules.pdf', { delivery_hash: delivery.hash })" target="_blank" class="text-blue-600 hover:text-blue-900 inline">
                      <DownloadIcon aria-hidden="true" class="-mx-1 h-5 w-5"/>
                    </a>
                  </div>
                  <span v-else>
                    <span class="text-xs">Exam token:</span><br>
                    <code v-if="delivery.status.toLowerCase() === 'scheduled' || delivery.status.toLowerCase() === 'on progress'">{{ delivery.token }}</code>
                    <span v-else>-</span>
                  </span>
                </td>
              </tr>
              <tr v-if="payload.data.length === 0">
                <td colspan="5">
                  <div class="text-center w-full my-4">No schedule, yet.</div>
                </td>
              </tr>
              </tbody>
            </table>
            <pagination :pagination-data="payload"/>
          </div>
        </div>
      </div>
    </div>
  </taker-layout>
</template>


