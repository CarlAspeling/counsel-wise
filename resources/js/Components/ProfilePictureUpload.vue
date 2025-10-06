<template>
    <div class="space-y-3">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Profile Picture</label>

        <!-- Current Profile Picture Preview -->
        <div class="flex items-center gap-4">
            <div class="relative">
                <img
                    :src="previewUrl || profilePictureUrl"
                    :alt="`${userName}'s profile picture`"
                    class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-700"
                />
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    JPG, PNG, or WebP. Max 5MB.
                </p>
            </div>
        </div>

        <!-- Upload Area -->
        <div
            @drop.prevent="handleDrop"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            :class="[
                'relative border-2 border-dashed rounded-lg p-4 transition-colors cursor-pointer',
                isDragging
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'
            ]"
            @click="$refs.fileInput.click()"
        >
            <input
                ref="fileInput"
                type="file"
                accept="image/jpeg,image/jpg,image/png,image/webp"
                @change="handleFileSelect"
                class="hidden"
            />

            <div class="text-center">
                <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="mt-2">
                    <span class="text-sm text-blue-600 dark:text-blue-400 font-medium">Click to upload</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400"> or drag and drop</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Changes will be saved when you click "Save Changes" below
                </p>
            </div>
        </div>

        <!-- Error Messages -->
        <div v-if="error" class="text-red-600 dark:text-red-400 text-sm">
            {{ error }}
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-4">
            <button
                v-if="previewUrl"
                type="button"
                @click="cancelUpload"
                class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-md text-sm font-medium"
            >
                Cancel
            </button>

            <button
                v-if="hasUploadedPicture && !previewUrl"
                type="button"
                @click="deleteImage"
                :disabled="deleteForm.processing"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md disabled:opacity-50 text-sm font-medium"
            >
                {{ deleteForm.processing ? 'Deleting...' : 'Remove Picture' }}
            </button>
        </div>

        <!-- Delete Success Message -->
        <div v-if="deleteForm.recentlySuccessful" class="text-green-600 dark:text-green-400 text-sm">
            Profile picture removed successfully!
        </div>

        <!-- Delete Error Message -->
        <div v-if="deleteForm.errors.profile_picture" class="text-red-600 dark:text-red-400 text-sm">
            {{ deleteForm.errors.profile_picture }}
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    profilePictureUrl: {
        type: String,
        required: true,
    },
    userName: {
        type: String,
        required: true,
    },
    hasUploadedPicture: {
        type: Boolean,
        default: false,
    },
    modelValue: {
        type: File,
        default: null,
    },
})

const emit = defineEmits(['update:modelValue'])

const fileInput = ref(null)
const previewUrl = ref(null)
const isDragging = ref(false)
const error = ref(null)

const deleteForm = useForm({})

// Watch for changes to profilePictureUrl (indicates successful upload)
// and clear the preview
watch(() => props.profilePictureUrl, (newUrl, oldUrl) => {
    if (newUrl !== oldUrl && previewUrl.value) {
        // Clear preview after successful upload
        previewUrl.value = null
        error.value = null
        emit('update:modelValue', null)
        if (fileInput.value) {
            fileInput.value.value = ''
        }
    }
})

const handleFileSelect = (event) => {
    const file = event.target.files[0]
    if (file) {
        processFile(file)
    }
}

const handleDrop = (event) => {
    isDragging.value = false
    const file = event.dataTransfer.files[0]
    if (file && file.type.startsWith('image/')) {
        processFile(file)
    }
}

const processFile = (file) => {
    // Client-side validation
    if (!['image/jpeg', 'image/jpg', 'image/png', 'image/webp'].includes(file.type)) {
        error.value = 'The file must be an image (JPEG, PNG, or WebP).'
        return
    }

    if (file.size > 5 * 1024 * 1024) {
        error.value = 'The image must not exceed 5MB.'
        return
    }

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
        previewUrl.value = e.target.result
    }
    reader.readAsDataURL(file)

    // Emit file to parent form
    emit('update:modelValue', file)

    // Clear any previous errors
    error.value = null
}

const deleteImage = () => {
    if (confirm('Are you sure you want to remove your profile picture?')) {
        deleteForm.delete(route('profile.picture.delete'), {
            preserveScroll: true,
        })
    }
}

const cancelUpload = () => {
    previewUrl.value = null
    error.value = null
    emit('update:modelValue', null)
    if (fileInput.value) {
        fileInput.value.value = ''
    }
}
</script>
