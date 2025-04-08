<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import FileUploader from '@/Components/FileUploader.vue';
import { useTicketFiles } from '@/Composables/useTicketFiles';
import { useNotification } from '@/Composables/useNotification';
import TicketFilesList from './TicketFilesList.vue';

const { handleFileChange, formatDate } = useTicketFiles();
const { success, error } = useNotification();

const props = defineProps({
    ticket: {
        type: Object,
        default: () => ({
            title: '',
            description: '',
            status: 'open',
            priority: 'medium',
            created_at: '',
            updated_at: '',
            followups: [],
            files: [],
        })
    },
    files: {
        type: Array,
        default: () => []
    },
    mode: {
        type: String,
        default: 'create',
        validator: (value) => ['create', 'edit', 'show'].includes(value)
    }
});

const emit = defineEmits(['save', 'cancel', 'file-change']);

const fileInput = ref(null);

const form = useForm({
    title: props.ticket?.title || '',
    description: props.ticket?.description || '',
    status: props.ticket?.status || 'open',
    priority: props.ticket?.priority || 'medium',
    created_at: props.ticket?.created_at || '',
    updated_at: props.ticket?.updated_at || '',
    files: [],
});

// Вычисляем режим работы формы
const isEdit = computed(() => props.mode === 'edit');
const isCreate = computed(() => props.mode === 'create');
const isShow = computed(() => props.mode === 'show');
const isDisabled = computed(() => props.mode === 'show');

// Форматируем даты создания и обновления
const createdDate = computed(() => formatDate(props.ticket?.created_at));
const updatedDate = computed(() => formatDate(props.ticket?.updated_at));

const onFileChange = (event) => {
    handleFileChange(event, form);
    emit('file-change', event);
};

const save = () => {
    if (isEdit.value) {
        form.put(route('ticket.update', props.ticket.id), {
            onSuccess: () => {
                success('Ticket updated successfully');
                emit('save', props.ticket.id);
            },
            onError: (errors) => {
                error('Failed to update ticket. Please check the form.', errors);
            }
        });
    } else if (isCreate.value) {
        form.post(route('ticket.store'), {
            onSuccess: () => {
                success('Ticket created successfully');
                form.reset();
                if (fileInput.value) {
                    fileInput.value.value = '';
                }
                emit('save');
            },
            onError: (errors) => {
                error('Failed to create ticket. Please check the form.', errors);
            }
        });
    }
};

const reset = () => {
    form.reset();
    form.clearErrors();
    emit('cancel');
};
</script>

<template>
    <form @submit.prevent="save" class="w-full min-h-full">
        <!-- Title -->
        <div class="mb-4">
            <label for="title" class="block text-sm font-medium"
                :class="{ 'text-gray-500': isShow, 'text-gray-700': !isShow }">Title</label>
            <input v-model="form.title" id="title" type="text" :disabled="isDisabled" :class="[
                'block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1',
                { 'cursor-not-allowed text-gray-500 bg-gray-200': isDisabled }
            ]" />
            <InputError :message="form.errors.title" class="mt-2" />
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium"
                :class="{ 'text-gray-500': isShow, 'text-gray-700': !isShow }">Description</label>
            <textarea v-model="form.description" id="description" :disabled="isDisabled" :class="[
                'block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1',
                { 'cursor-not-allowed text-gray-500 bg-gray-200': isDisabled }
            ]" rows="4"></textarea>
            <InputError :message="form.errors.description" class="mt-2" />
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium"
                :class="{ 'text-gray-500': isShow, 'text-gray-700': !isShow }">Status</label>
            <select v-model="form.status" id="status" :disabled="isDisabled" :class="[
                'block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1',
                { 'cursor-not-allowed text-gray-500 bg-gray-200': isDisabled }
            ]">
                <option value="open">Open</option>
                <option value="in_progress">In Progress</option>
                <option value="closed">Closed</option>
            </select>
            <InputError :message="form.errors.status" class="mt-2" />
        </div>

        <!-- Priority -->
        <div class="mb-4">
            <label for="priority" class="block text-sm font-medium"
                :class="{ 'text-gray-500': isShow, 'text-gray-700': !isShow }">Priority</label>
            <select v-model="form.priority" id="priority" :disabled="isDisabled" :class="[
                'block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1',
                { 'cursor-not-allowed text-gray-500 bg-gray-200': isDisabled }
            ]">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
            <InputError :message="form.errors.priority" class="mt-2" />
        </div>

        <!-- File Uploader -->
        <div class="mb-4" v-if="isCreate">
            <FileUploader
                v-model="form.files"
                :errors="form.errors"
                fieldName="files"
                :input-ref="fileInput"
                @file-change="onFileChange"
            />
        </div>

        <!-- Список файлов -->
        <div class="mb-4" v-if="(isEdit || isShow) && props.files.length > 0">
            <TicketFilesList 
                :files="props.files"
                :is-read-only="isShow"
            />
        </div>
        <div v-else class="mb-4 text-sm text-gray-500">
            No attached files.
        </div>

        <template v-if="isEdit || isShow">
            <div class="flex justify-between">
                <div class="mb-4">
                    <label class="block text-sm font-medium"
                        :class="{ 'text-gray-500': isShow, 'text-gray-700': !isShow }">Created At</label>
                    <span :class="{ 'text-gray-500': isShow, 'text-gray-900': !isShow }">{{ createdDate
                        }}</span>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium"
                        :class="{ 'text-gray-500': isShow, 'text-gray-700': !isShow }">Updated At</label>
                    <span :class="{ 'text-gray-500': isShow, 'text-gray-900': !isShow }">{{ updatedDate
                        }}</span>
                </div>
            </div>
        </template>

        <div v-if="isEdit || isCreate" class="flex justify-center gap-2 mt-6 button-container">
            <button 
                type="button" 
                @click="reset" 
                :disabled="form.processing" 
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 justify-center w-full sm:w-auto min-w-[100px]"
            >
                Cancel
            </button>
            <button 
                type="submit" 
                :disabled="form.processing" 
                class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 justify-center w-full sm:w-auto min-w-[100px]"
            >
                {{ isEdit ? 'Update Ticket' : 'Create Ticket' }}
            </button>
        </div>
    </form>
</template>

<style scoped>
@media (max-width: 640px) {
    .button-container {
        flex-direction: column;
        width: 100%;
    }
    
    .button-container button {
        margin-bottom: 0.5rem;
    }
}
</style> 