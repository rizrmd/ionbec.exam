<template>
  <dashboard-layout title="Dashboard">
    <div class="mb-8 max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="col-span-4 flex flex-row gap-2 bg-orange-50 text-orange-600 px-2.5 py-2 rounded-md border border-orange-200 text-sm"><ExclamationCircleIcon class="w-4"/> Please contact Server Administrator to increase max participants. Upgrading may takes 30 minutes.</div>
      <card-counter title="CPU" :icon="ChipIcon" :count="serverInfo.cpu.count" suffix="Core(s)"/>
      <card-counter title="RAM" :icon="ServerIcon" :count="serverInfo.ram.total" suffix="GB"/>
      <card-counter title="Disk" :icon="DesktopComputerIcon" :count="serverInfo.disk.total"/>
      <card-counter title="Max. Participants" :icon="UserGroupIcon" :count="Math.round(serverInfo.ram.total / 0.145)" prefix="(recommended)"/>
    </div>

    <div class="max-w-full mx-auto sm:px-6 lg:px-0 grid grid-cols-1 md:grid-cols-4 gap-4">
      <card-counter title="Total Categories" :icon="TagIcon" :count="countCategory" @click="Inertia.visit(route('back-office.category.index'))"/>
      <card-counter title="Total Question Sets" :icon="BadgeCheckIcon" :count="countItem" @click="Inertia.visit(route('back-office.question-set.index'))"/>
      <card-counter title="Total Questions" :icon="SearchCircleIcon" :count="countQuestion" @click="Inertia.visit(route('back-office.question-pack.index'))"/>
      <card-counter title="Total Tests" :icon="ClipboardListIcon" :count="countTest" @click="Inertia.visit(route('back-office.test.index'))"/>
    </div>

    <div class="max-w-full py-4">
      <h2 class="font-bold py-2">Quick Actions</h2>
      <div class="sm:px-6 lg:px-0 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">
        <Link v-for="(action, index) in quickActions"
              :key="`quick-action-${index}`"
              :href="route(action.route, {modal_create: true})"
              class="inline-flex items-center px-3 py-4 border border-transparent shadow-sm text-sm leading-4
             font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2
             focus:ring-offset-2 focus:ring-primary-500">
          <plus-icon aria-hidden="true" class="-ml-0.5 mr-2 h-4 w-4"/>
          {{ action.name }}
        </Link>
      </div>
    </div>

    <div class="max-w-full py-4 flex flex-col md:flex-row gap-4">
      <div v-if="upcomingDelivery.length > 0" class="flex-1">
        <h2 class="font-bold py-2">Upcoming Deliveries</h2>
        <div class="py-2 grid grid-cols-1 gap-4">
          <card v-for="delivery in upcomingDelivery" :key="`deliveries-${delivery.hash}`" class="hover:bg-gray-200 cursor-normal">
            <div class="flex justify-between">
              <h4 class="font-bold">{{ delivery.name }}</h4>
              <small class="float-right">{{ moment(delivery.scheduled_at).format('DD MMM YYYY') }} <span
                class="text-gray-600">{{ moment(delivery.scheduled_at).format('HH:mm') }}</span></small>
            </div>
            <div class="mt-1 text-sm text-gray-600"><div style="min-width: 64px; display: inline-block;">Group</div>: {{ delivery.group.name }}</div>
            <div class="text-sm text-gray-600"><div style="min-width: 64px; display: inline-block;">Exam</div>: {{ delivery.exam.name }} - {{ delivery.exam.code }}</div>
            <div class="text-sm mt-1">
              {{ delivery.takers_count }} Takers
            </div>
          </card>
        </div>
      </div>
      <div v-if="logs.length > 0">
        <h2 class="font-bold py-2">Recent Activities</h2>
        <div class="flow-root py-2 lg:pl-8">
          <ul class="-mb-8" role="list">
            <li v-for="(activity, index) in logs" :key="`activity-item-${activity.id}`">
              <div class="relative pb-8">
                <span v-if="index !== logs.length - 1"
                      aria-hidden="true" class="absolute top-0.5 left-1.5 -ml-px h-full w-0.5 bg-gray-200"/>
                <div class="relative flex items-start space-x-3">
                  <div class="relative">
                    <span
                      class="h-3 w-3 rounded-full bg-primary-600 flex items-center justify-center ring-2 ring-white mt-1"></span>
                  </div>
                  <div class="min-w-0 flex-1">
                    <div>
                      <div class="text-sm">
                        <a href="#" class="font-medium text-gray-900">{{ activity.causer ? activity.causer.name : '-' }}</a>
                      </div>
                      <p class="mt-0.5 text-sm text-gray-500">{{ moment(activity.created_at).format('DD MMM YYYY') }}
                        <span class="text-gray-600">{{ moment(activity.created_at).format('HH:mm') }}</span></p>
                    </div>
                    <div class="mt-2 text-sm text-gray-700">
                      <p v-html="activity.description"></p>
                    </div>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </dashboard-layout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import Card from '@/Components/Card'
import CardCounter from '@/Components/CardCounter'
import {PlusIcon, TagIcon, ExclamationCircleIcon, BadgeCheckIcon, SearchCircleIcon, ClipboardListIcon, ChipIcon, ServerIcon, DesktopComputerIcon, UserGroupIcon} from '@heroicons/vue/outline'
import {Link} from '@inertiajs/inertia-vue3';
import {toRefs} from "vue";
import moment from "moment";
import {Inertia} from "@inertiajs/inertia";

const props = defineProps({
  countCategory: {
    type: Number,
    default: 0,
  },
  countItem: {
    type: Number,
    default: 0,
  },
  countQuestion: {
    type: Number,
    default: 0,
  },
  countTest: {
    type: Number,
    default: 0,
  },
  upcomingDelivery: {
    type: Object,
  },
  logs: {
    type: Object,
  },
  serverInfo: {
    type: Object,
  }
});

const {logs} = toRefs(props)

// onMounted(() => {
//   console.log(logs.value)
// })

const activity = [
  {
    id: 1,
    type: 'comment',
    person: {name: 'Eduardo Benz', href: '#'},
    imageUrl:
      'https://images.unsplash.com/photo-1520785643438-5bf77931f493?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=256&h=256&q=80',
    comment:
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Tincidunt nunc ipsum tempor purus vitae id. Morbi in vestibulum nec varius. Et diam cursus quis sed purus nam. ',
    date: '6d ago',
  },
  {
    id: 4,
    type: 'comment',
    person: {name: 'Jason Meyers', href: '#'},
    imageUrl:
      'https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?ixlib=rb-=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=8&w=256&h=256&q=80',
    comment:
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Tincidunt nunc ipsum tempor purus vitae id. Morbi in vestibulum nec varius. Et diam cursus quis sed purus nam. Scelerisque amet elit non sit ut tincidunt condimentum. Nisl ultrices eu venenatis diam.',
    date: '2h ago',
  },
]

const quickActions = [
  {
    route: 'back-office.delivery.index',
    name: 'New Delivery',
  },
  {
    route: 'back-office.group.index',
    name: 'New Group',
  },
  {
    route: 'back-office.test-taker.index',
    name: 'Register Candidate',
  },
  {
    route: 'back-office.test.index',
    name: 'New Test',
  },
  {
    route: 'back-office.category.index',
    name: 'New Question Category',
  },
  {
    route: 'back-office.question-set.index',
    name: 'New Question Set',
  },
]

</script>
