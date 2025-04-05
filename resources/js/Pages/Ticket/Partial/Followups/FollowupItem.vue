<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
    followup: {
        type: Object,
        required: true
    },
    isEdit: {
        type: Boolean,
        default: false
    },
    currentUser: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['delete', 'update']);

const isEditing = ref(false);
const editForm = useForm({
    content: props.followup.content,
    type: props.followup.type,
    followup_id: props.followup.id
});

const formatDate = (timestamp) => {
    if (!timestamp) return '';
    return new Date(timestamp).toLocaleString();
};

// Функция для форматирования размера файла
const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    else if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    else return (bytes / 1048576).toFixed(2) + ' MB';
};

const startEditing = () => {
    isEditing.value = true;
};

const cancelEditing = () => {
    isEditing.value = false;
    editForm.reset();
    editForm.clearErrors();
};
</script>

<template>
    <div class="bg-white shadow rounded-lg p-4 mb-4">
        <div class="flex justify-between">
            <div class="flex flex-col">
                <span class="font-medium">{{ followup.user.name }}</span>
                <div>
                    <span class="text-sm text-gray-500">{{ formatDate(followup.created_at) }}</span>
                    <span v-if="followup.created_at !== followup.updated_at" class="text-sm text-gray-400"> - edited</span>
                </div>
            </div>
            <div class="flex flex-col items-end">
                <span :class="followup.type === 'comment' ? 'bg-yellow-500' : 'bg-blue-500'" class="text-white px-2 py-1 rounded text-xs">
                    {{ followup.type }}
                </span>
                <div class="flex justify-end gap-1 mt-2" v-if="isEdit">
                    <button 
                        v-if="followup.user_id === currentUser.id"
                        class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
                        @click="startEditing"
                        title="Edit"
                    >
                        <font-awesome-icon
                            icon="fa-solid fa-pen-to-square"
                            class="h-4 w-4 opacity-70"
                        />
                    </button>
                    <button 
                        v-if="currentUser.is_admin || followup.user_id === currentUser.id"
                        class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
                        @click="$emit('delete', followup.id)"
                        title="Delete"
                    >
                        <font-awesome-icon
                            icon="fa-solid fa-trash-can"
                            class="h-4 w-4 opacity-70"
                        />
                    </button>
                </div>
            </div>
        </div>
        
        <div v-if="isEditing">
            <textarea v-model="editForm.content"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-4"
                rows="4"></textarea>
            <InputError :message="editForm.errors.content" class="mt-2" />
            <div class="flex justify-end gap-2 mt-2">
                <button 
                    @click="cancelEditing"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Cancel
                </button>
                <button 
                    @click="$emit('update', editForm)"
                    class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Save
                </button>
            </div>
        </div>
        <p v-else class="mt-2 text-gray-700">{{ followup.content }}</p>
    </div>
</template>
