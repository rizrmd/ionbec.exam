<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-100">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Exam Login
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Enter your exam token to continue
                </p>
            </div>

            <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="token" class="sr-only">Exam Token</label>
                        <input
                            id="token"
                            name="token"
                            type="text"
                            required
                            v-model="form.token"
                            class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm text-center text-xl font-mono uppercase"
                            placeholder="Enter your token"
                            maxlength="10"
                            style="letter-spacing: 0.1em;"
                        />
                    </div>
                </div>

                <div v-if="success" class="rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                {{ success }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-if="error" class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                {{ error }}
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="animate-spin h-5 w-5 text-blue-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        {{ loading ? 'Signing in...' : 'Sign In' }}
                    </button>
                </div>

                <div class="text-center">
                    <p class="text-xs text-gray-500">
                        Your exam token should be 5 characters long (e.g., 3AfDf)
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Contact your administrator if you don't have a token
                    </p>
                </div>
            </form>

            <div class="mt-6 border-t border-gray-200 pt-6">
                <div class="text-center text-sm text-gray-600">
                    <p class="mb-2">Quick Access Examples:</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <button
                            @click="form.token = '3AfDf'"
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs font-mono transition-colors"
                        >
                            3AfDf
                        </button>
                        <button
                            @click="form.token = 'DEMO1'"
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs font-mono transition-colors"
                        >
                            DEMO1
                        </button>
                        <button
                            @click="form.token = 'TEST2'"
                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-xs font-mono transition-colors"
                        >
                            TEST2
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/inertia-vue3'

const form = reactive({
    token: ''
})

const loading = ref(false)
const error = ref('')
const success = ref('')

const page = computed(() => usePage().props.value)

onMounted(() => {
    const flash = page.value.flash

    if (flash?.success) {
        success.value = flash.success
    }

    if (flash?.error) {
        error.value = flash.error
    }

    // Get token from URL if present
    const urlParams = new URLSearchParams(window.location.search)
    const token = urlParams.get('token')
    if (token) {
        form.token = token
    }
})

const handleSubmit = async () => {
    if (!form.token.trim()) {
        error.value = 'Please enter a token'
        return
    }

    loading.value = true
    error.value = ''
    success.value = ''

    // Direct redirect approach since POST route may not be registered yet
    window.location.href = `/exam/${form.token.trim()}`
}
</script>