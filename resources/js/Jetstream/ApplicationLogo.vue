<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/inertia-vue3'
import logo from '@images/logo.png'

const client = computed(() => usePage().props.value.client)
const clientLogo = computed(() => client.value?.logo_url || logo)
const clientName = computed(() => client.value?.name || 'IONBEC')
</script>

<template>
  <img :src="clientLogo" :alt="clientName">
</template>
