<template>
  <JetDialogModal :show="show" @close="closeModal" max-width="2xl">
    <template #title>
      <div v-if="!isCloning">Clone Client: {{ client.name }}</div>
      <div v-else>Cloning {{ client.name }}...</div>
    </template>

    <template #content>
      <!-- Clone Form -->
      <div v-if="!isCloning" class="space-y-4">
        <div>
          <label for="clone-name" class="block text-sm font-medium text-gray-700">New Client Name</label>
          <input 
            id="clone-name" 
            v-model="form.name" 
            type="text" 
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="Enter new client name"
            @input="generateSlug"
            :class="{'border-red-300': form.errors.name}"
          />
          <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</div>
        </div>

        <div>
          <label for="clone-slug" class="block text-sm font-medium text-gray-700">New Client Slug</label>
          <input 
            id="clone-slug" 
            v-model="form.slug" 
            type="text" 
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
            placeholder="client-slug"
            :class="{'border-red-300': form.errors.slug}"
          />
          <div v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</div>
        </div>

        <div class="border-t pt-4">
          <h4 class="text-sm font-medium text-gray-900 mb-3">What to Clone</h4>
          <div class="space-y-2">
            <label class="flex items-center">
              <input 
                type="checkbox" 
                v-model="form.options.clone_categories"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <span class="ml-2 text-sm text-gray-700">Categories ({{ client.categories_count || 0 }})</span>
            </label>
            
            <label class="flex items-center">
              <input 
                type="checkbox" 
                v-model="form.options.clone_questions"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <span class="ml-2 text-sm text-gray-700">Questions ({{ client.questions_count || 0 }})</span>
            </label>
            
            <label class="flex items-center">
              <input 
                type="checkbox" 
                v-model="form.options.clone_exams"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <span class="ml-2 text-sm text-gray-700">Exams ({{ client.exams_count || 0 }})</span>
            </label>
            
            <label class="flex items-center">
              <input 
                type="checkbox" 
                v-model="form.options.clone_groups"
                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              />
              <span class="ml-2 text-sm text-gray-700">Groups ({{ client.groups_count || 0 }})</span>
            </label>
          </div>
          
          <div class="mt-3 p-3 bg-blue-50 rounded-md">
            <p class="text-xs text-blue-800">
              <strong>Note:</strong> Domains will not be cloned and must be configured manually. 
              Takers will not be cloned to groups. Exams will be set as drafts.
            </p>
          </div>
        </div>
      </div>

      <!-- Progress Display -->
      <div v-else class="space-y-4">
        <div class="text-center">
          <div class="mb-2">
            <div class="text-lg font-medium text-gray-900">{{ progress.percentage }}%</div>
          </div>
          
          <!-- Progress Bar -->
          <div class="w-full bg-gray-200 rounded-full h-3 mb-4">
            <div 
              class="bg-primary-600 h-3 rounded-full transition-all duration-300 ease-out"
              :class="{'bg-red-600': progress.has_error, 'bg-green-600': progress.is_complete && !progress.has_error}"
              :style="`width: ${Math.max(0, progress.percentage)}%`"
            ></div>
          </div>
          
          <!-- Status Message -->
          <div class="text-sm text-gray-600 mb-2">{{ progress.message }}</div>
          
          <!-- Time Information -->
          <div v-if="timeInfo.elapsed" class="text-xs text-gray-500 space-x-4">
            <span>Elapsed: {{ formatTime(timeInfo.elapsed) }}</span>
            <span v-if="timeInfo.estimated && progress.percentage > 0 && progress.percentage < 100">
              Est. remaining: {{ formatTime(timeInfo.estimated) }}
            </span>
          </div>
        </div>

        <!-- Error Display -->
        <div v-if="progress.has_error" class="p-3 bg-red-50 border border-red-200 rounded-md">
          <div class="text-sm text-red-800">
            <strong>Clone Failed:</strong> {{ progress.message }}
          </div>
        </div>

        <!-- Success Display -->
        <div v-if="progress.is_complete && !progress.has_error" class="p-3 bg-green-50 border border-green-200 rounded-md">
          <div class="text-sm text-green-800">
            <strong>Success!</strong> Client "{{ progress.data.client_name }}" has been cloned successfully.
          </div>
        </div>
      </div>
    </template>

    <template #footer>
      <div v-if="!isCloning" class="flex justify-end space-x-2">
        <button
          type="button"
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          @click="closeModal"
        >
          Cancel
        </button>
        
        <button
          type="button"
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          :disabled="form.processing || !form.name || !form.slug"
          @click="startCloning"
        >
          <DocumentDuplicateIcon v-if="!form.processing" class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
          <svg v-else class="-ml-1 mr-2 h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ form.processing ? 'Starting...' : 'Start Cloning' }}
        </button>
      </div>
      
      <div v-else class="flex justify-end space-x-2">
        <button
          v-if="progress.is_complete || progress.has_error"
          type="button"
          class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
          @click="closeModal"
        >
          Close
        </button>
        
        <button
          v-if="progress.is_complete && !progress.has_error && progress.data.client_id"
          type="button"
          class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          @click="goToNewClient"
        >
          Go to New Client
        </button>
      </div>
    </template>
  </JetDialogModal>
</template>

<script setup>
import JetDialogModal from '@/Jetstream/DialogModal.vue'
import { DocumentDuplicateIcon } from '@heroicons/vue/outline'
import { useForm } from '@inertiajs/inertia-vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  client: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'cloned'])

const form = useForm({
  name: '',
  slug: '',
  options: {
    clone_categories: true,
    clone_questions: true,
    clone_exams: true,
    clone_groups: true,
  }
})

const isCloning = ref(false)
const progress = reactive({
  percentage: 0,
  message: '',
  data: {},
  is_complete: false,
  has_error: false
})

const timeInfo = reactive({
  startTime: null,
  elapsed: 0,
  estimated: 0
})

let progressInterval = null
let echoChannel = null

const generateSlug = async () => {
  if (form.name && form.name.length > 2) {
    try {
      const response = await axios.get(`/back-office/clients/suggest-slug/${encodeURIComponent(form.name)}`)
      form.slug = response.data.slug
    } catch (error) {
      console.error('Error generating slug:', error)
    }
  }
}

const startCloning = async () => {
  form.processing = true
  form.clearErrors()
  
  try {
    const response = await axios.post(`/back-office/clients/${props.client.id}/clone`, {
      name: form.name,
      slug: form.slug,
      options: form.options
    })
    
    if (response.data.success && response.data.job_id) {
      isCloning.value = true
      timeInfo.startTime = Date.now()
      
      // Start listening for progress updates
      startProgressTracking(response.data.job_id)
      
      // Start time tracking
      progressInterval = setInterval(() => {
        if (timeInfo.startTime) {
          timeInfo.elapsed = Date.now() - timeInfo.startTime
          
          // Estimate remaining time based on progress
          if (progress.percentage > 0 && progress.percentage < 100) {
            const timePerPercent = timeInfo.elapsed / progress.percentage
            timeInfo.estimated = timePerPercent * (100 - progress.percentage)
          }
        }
      }, 1000)
    }
  } catch (error) {
    console.error('Clone request failed:', error)
    if (error.response?.data?.errors) {
      Object.keys(error.response.data.errors).forEach(key => {
        form.setError(key, error.response.data.errors[key][0])
      })
    }
  } finally {
    form.processing = false
  }
}

const startProgressTracking = (jobId) => {
  // Setup Laravel Echo listener (assuming Echo is available globally)
  if (window.Echo) {
    echoChannel = window.Echo.channel(`client-clone.${jobId}`)
      .listen('.progress', (e) => {
        progress.percentage = e.percentage
        progress.message = e.message
        progress.data = e.data || {}
        progress.is_complete = e.is_complete || false
        progress.has_error = e.has_error || false
        
        if (progress.is_complete || progress.has_error) {
          stopProgressTracking()
        }
      })
  }
}

const stopProgressTracking = () => {
  if (progressInterval) {
    clearInterval(progressInterval)
    progressInterval = null
  }
  
  if (echoChannel) {
    window.Echo.leave(echoChannel.name)
    echoChannel = null
  }
}

const formatTime = (milliseconds) => {
  const seconds = Math.floor(milliseconds / 1000)
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  
  if (minutes > 0) {
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
  }
  return `0:${remainingSeconds.toString().padStart(2, '0')}`
}

const goToNewClient = () => {
  if (progress.data.client_id) {
    Inertia.visit(`/back-office/clients/${progress.data.client_id}`)
  }
}

const closeModal = () => {
  stopProgressTracking()
  
  // Reset form and progress
  form.reset()
  form.clearErrors()
  isCloning.value = false
  progress.percentage = 0
  progress.message = ''
  progress.data = {}
  progress.is_complete = false
  progress.has_error = false
  timeInfo.startTime = null
  timeInfo.elapsed = 0
  timeInfo.estimated = 0
  
  emit('close')
  
  if (progress.is_complete && !progress.has_error) {
    emit('cloned')
  }
}

// Set default name when client changes
watch(() => props.client, (newClient) => {
  if (newClient && !isCloning.value) {
    form.name = `${newClient.name} (Copy)`
    generateSlug()
  }
}, { immediate: true })

// Cleanup on unmount
onUnmounted(() => {
  stopProgressTracking()
})
</script>