<script setup>
import TakerLayout from '@/Layouts/TakerLayout.vue'
import moment from 'moment'
import Card from '@/Components/Card'

const props = defineProps({
  data: {
    type: Object
  },
  upcomingDelivery: {
    type: Object
  },
});

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
</script>

<template>
  <taker-layout title="Dashboard">
    <div class="max-w-full pb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <h2 class="font-bold py-2">Upcoming Deliveries</h2>
        <div class="py-2 grid grid-cols-1 gap-4">
          <card v-for="delivery in upcomingDelivery" :key="`deliveries-${delivery.hash}`">
            <div class="flex justify-between">
              <h4 class="font-bold">{{ delivery.display_name }}</h4>
              <small class="float-right">{{ moment(delivery.scheduled_at).format('DD MMM YYYY') }} <span class="text-gray-600">{{ moment(delivery.scheduled_at).format('HH:mm') }}</span></small>
            </div>
          </card>
        </div>
      </div>
      <div>
<!--        <h2 class="font-bold py-2">Recent Activities</h2>-->
<!--        <div class="flow-root py-2 pl-8">-->
<!--          <ul class="-mb-8" role="list">-->
<!--            <li v-for="(activityItem, activityItemIdx) in activity" :key="`activity-item-${activityItem.id}`">-->
<!--              <div class="relative pb-8">-->
<!--                <span v-if="activityItemIdx !== activity.length - 1"-->
<!--                      aria-hidden="true" class="absolute top-0.5 left-1.5 -ml-px h-full w-0.5 bg-gray-200"/>-->
<!--                <div class="relative flex items-start space-x-3">-->
<!--                  <template v-if="activityItem.type === 'comment'">-->
<!--                    <div class="relative">-->
<!--                      <span class="h-3 w-3 rounded-full bg-indigo-600 flex items-center justify-center ring-2 ring-white mt-1"></span>-->
<!--                    </div>-->
<!--                    <div class="min-w-0 flex-1">-->
<!--                      <div>-->
<!--                        <div class="text-sm">-->
<!--                          <a :href="activityItem.person.href"-->
<!--                             class="font-medium text-gray-900">{{ activityItem.person.name }}</a>-->
<!--                        </div>-->
<!--                        <p class="mt-0.5 text-sm text-gray-500">{{ activityItem.date }}</p>-->
<!--                      </div>-->
<!--                      <div class="mt-2 text-sm text-gray-700">-->
<!--                        <p>-->
<!--                          {{ activityItem.comment }}-->
<!--                        </p>-->
<!--                      </div>-->
<!--                    </div>-->
<!--                  </template>-->
<!--                </div>-->
<!--              </div>-->
<!--            </li>-->
<!--          </ul>-->
<!--        </div>-->
      </div>
    </div>
  </taker-layout>
</template>


