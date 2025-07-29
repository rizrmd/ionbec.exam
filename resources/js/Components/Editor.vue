<template>
  <div v-if="editor" :class="[mode === 'basic' ? 'shadow ring-1 ring-black ring-opacity-5 rounded-lg p-3' : '']">
    <div class="flex flex-wrap gap-5" v-if="mode === 'basic'">
      <div class="flex flex-wrap gap-2">
        <button @click="editor.chain().focus().toggleBold().run()" :class="['px-2 py-1 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500', editor.isActive('bold') ? 'text-white bg-primary-600 hover:bg-primary-700' : 'bg-gray-200 hover:bg-gray-300']">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M14 4H6V12H14C15.0609 12 16.0783 11.5786 16.8284 10.8284C17.5786 10.0783 18 9.06087 18 8C18 6.93913 17.5786 5.92172 16.8284 5.17157C16.0783 4.42143 15.0609 4 14 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 12H6V20H15C16.0609 20 17.0783 19.5786 17.8284 18.8284C18.5786 18.0783 19 17.0609 19 16C19 14.9391 18.5786 13.9217 17.8284 13.1716C17.0783 12.4214 16.0609 12 15 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button @click="editor.chain().focus().toggleItalic().run()" :class="['px-2 py-1 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500', editor.isActive('italic') ? 'text-white bg-primary-600 hover:bg-primary-700' : 'bg-gray-200 hover:bg-gray-300']">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 4H10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M14 20H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M15 4L9 20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button @click="editor.chain().focus().toggleUnderline().run()" :class="['px-2 py-1 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500', editor.isActive('underline') ? 'text-white bg-primary-600 hover:bg-primary-700' : 'bg-gray-200 hover:bg-gray-300']">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 3V10C6 11.5913 6.63214 13.1174 7.75736 14.2426C8.88258 15.3679 10.4087 16 12 16C13.5913 16 15.1174 15.3679 16.2426 14.2426C17.3679 13.1174 18 11.5913 18 10V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M4 21H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
      <div class="flex flex-wrap gap-2">
        <button @click="editor.chain().focus().setTextAlign('left').run()" :class="['px-2 py-1 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500', editor.isActive({ textAlign: 'left' }) ? 'text-white bg-primary-600 hover:bg-primary-700' : 'bg-gray-200 hover:bg-gray-300']">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17 10H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 6H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 14H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M17 18H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button @click="editor.chain().focus().setTextAlign('center').run()" :class="['px-2 py-1 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500', editor.isActive({ textAlign: 'center' }) ? 'text-white bg-primary-600 hover:bg-primary-700' : 'bg-gray-200 hover:bg-gray-300']">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 10H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 6H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 14H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M18 18H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button @click="editor.chain().focus().setTextAlign('right').run()" :class="['px-2 py-1 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500', editor.isActive({ textAlign: 'right' }) ? 'text-white bg-primary-600 hover:bg-primary-700' : 'bg-gray-200 hover:bg-gray-300']">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 10H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 6H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 14H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 18H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button @click="editor.chain().focus().setTextAlign('justify').run()" :class="['px-2 py-1 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500', editor.isActive({ textAlign: 'justify' }) ? 'text-white bg-primary-600 hover:bg-primary-700' : 'bg-gray-200 hover:bg-gray-300']">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21 6H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 14H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 10H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21 18H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
        <button @click="editor.chain().focus().toggleBulletList().run()" :class="['px-2 py-1 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500', editor.isActive({ textAlign: 'bulletList' }) ? 'text-white bg-primary-600 hover:bg-primary-700' : 'bg-gray-200 hover:bg-gray-300']">
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <g>
              <path fill="none" d="M0 0h24v24H0z"></path>
              <path d="M8 4h13v2H8V4zM4.5 6.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm0 7a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm0 6.9a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zM8 11h13v2H8v-2zm0 7h13v2H8v-2z"></path>
            </g>
          </svg>
        </button>
        <button @click="editor.chain().focus().toggleOrderedList().run()" :class="['px-2 py-1 border border-transparent shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500', editor.isActive({ textAlign: 'orderedList' }) ? 'text-white bg-primary-600 hover:bg-primary-700' : 'bg-gray-200 hover:bg-gray-300']">
          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
            <g>
              <path fill="none" d="M0 0h24v24H0z"></path>
              <path d="M8 4h13v2H8V4zM5 3v3h1v1H3V6h1V4H3V3h2zM3 14v-2.5h2V11H3v-1h3v2.5H4v.5h2v1H3zm2 5.5H3v-1h2V18H3v-1h3v4H3v-1h2v-.5zM8 11h13v2H8v-2zm0 7h13v2H8v-2z"></path>
            </g>
          </svg>
        </button>
      </div>
    </div>

    <div :class="[mode === 'basic' ? 'mt-3' : '']">
      <editor-content :editor="editor" :class="mode" />
    </div>
  </div>
</template>

<script>
import { Editor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'

export default {
  components: {
    EditorContent,
  },

  emits: ['blur'],

  props: {
    modelValue: {
      type: String,
      default: '',
    },
    mode: {
      type: String,
      default: 'basic',
    }
  },

  data() {
    return {
      editor: null,
    }
  },

  watch: {
    modelValue(value) {
      // HTML
      const isSame = this.editor.getHTML() === value

      // JSON
      // const isSame = JSON.stringify(this.editor.getJSON()) === JSON.stringify(value)

      if (isSame) {
        return
      }

      this.editor.commands.setContent(value, false)
    },
  },

  mounted() {
    this.editor = new Editor({
      extensions: [
        StarterKit.configure({
          bulletList: {
            HTMLAttributes: {
              class: 'list-disc ml-4',
            },
          },
          orderedList: {
            HTMLAttributes: {
              class: 'list-decimal ml-4',
            },
          },
        }),
        Underline,
        TextAlign.configure({
          types: ['heading', 'paragraph'],
        }),
      ],
      content: this.modelValue,
      // autofocus: true,
      // editable: true,
      // injectCSS: false,
      onUpdate: () => {
        // HTML
        this.$emit('update:modelValue', this.editor.getHTML())

        // JSON
        // this.$emit('update:modelValue', this.editor.getJSON())
      },

      onBlur: ({editor, event}) => {
        setTimeout(() => this.$emit('blur'), 200)
      }
    })
  },

  beforeUnmount() {
    this.editor.destroy()
  },
}
</script>

<style>
.ProseMirror {
  background: #f3f4f6;
  padding: 10px 15px;
  border-radius: 0.5rem;
  outline: none;
}

.basic > .ProseMirror{
  min-height: 100px;
}
</style>
