<template>
  <div class="bg-white overflow-hidden shadow-md sm:rounded-md flex flex-col hover:bg-gray-50 cursor-pointer">
    <div class="flex flex-row grow p-4">
      <component v-if="icon !== null" :is="icon" class="h-12" :class="'text-'+color"/>
      <div class="grow">
        <h2 class="text-2xl font-bold text-right"><span class="text-xs text-gray-400 font-normal">{{ prefix }}</span>
          {{ count }} <span class="text-xs text-gray-400 font-normal">{{ suffix }}</span></h2>
        <p class="text-sm text-right">{{ title }}</p>
      </div>
    </div>
    <div class="h-1" :class="'bg-'+color"/>
  </div>
</template>

<script>
export default {
  name: "Card",
  props: {
    title: {
      default: null,
      type: String
    },
    count: {
      default: 0,
      type: Number
    },
    icon: {
      default: null,
      type: Function
    },
    prefix: {
      default: null,
      type: String
    },
    suffix: {
      default: null,
      type: String
    },
    color: {
      default: 'gray-200',
      type: String
    }
  }
}
</script>

<style scoped>

</style>
