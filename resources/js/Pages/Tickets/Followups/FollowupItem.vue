<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/Button.vue';

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
            <div>
                <span :class="followup.type === 'comment' ? 'bg-yellow-500' : 'bg-blue-500'" class="text-white px-2 py-1 rounded">
                    {{ followup.type }}
                </span>
                <div class="flex justify-end" v-if="isEdit">
                    <Button v-if="followup.user_id === currentUser.id"
                        class="text-secondary" @click="startEditing">
                        <i class="pi pi-pencil"></i>
                    </Button>
                    <Button v-if="currentUser.is_admin || followup.user_id === currentUser.id"
                        class="text-danger" @click="$emit('delete', followup.id)">
                        <i class="pi pi-trash"></i>
                    </Button>
                </div>
            </div>
        </div>
        
        <div v-if="isEditing">
            <textarea v-model="editForm.content"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                rows="4"></textarea>
            <InputError :message="editForm.errors.content" class="mt-2" />
            <div class="flex justify-end gap-2 mt-2">
                <Button class="text-secondary" @click="cancelEditing">Cancel</Button>
                <Button class="text-primary" @click="$emit('update', editForm)">Save</Button>
            </div>
        </div>
        <p v-else class="mt-2 text-gray-700">{{ followup.content }}</p>
    </div>
</template>
