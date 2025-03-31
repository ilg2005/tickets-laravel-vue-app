<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

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
                <Tag :style="{ backgroundColor: followup.type === 'comment' ? '#FFBF00' : '#1E90FF' }" style="color: white">
                    {{ followup.type }}
                </Tag>
                <div class="flex justify-end" v-if="isEdit">
                    <Button v-if="followup.user_id === currentUser.id"
                        icon="pi pi-pencil" text size="small" severity="secondary"
                        @click="startEditing" />
                    <Button v-if="currentUser.is_admin || followup.user_id === currentUser.id"
                        icon="pi pi-trash" text size="small" severity="danger"
                        @click="$emit('delete', followup.id)" />
                </div>
            </div>
        </div>
        
        <div v-if="isEditing">
            <textarea v-model="editForm.content"
                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                rows="4"></textarea>
            <InputError :message="editForm.errors.content" class="mt-2" />
            <div class="flex justify-end gap-2 mt-2">
                <Button label="Cancel" severity="secondary" @click="cancelEditing" />
                <Button label="Save" severity="primary" @click="$emit('update', editForm)" />
            </div>
        </div>
        <p v-else class="mt-2 text-gray-700">{{ followup.content }}</p>
    </div>
</template>
