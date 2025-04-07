<script setup>
import { useForm, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { computed, ref, watch } from 'vue';
import { usePermission } from '@/Composables/permissions.js';
import { usePage } from "@inertiajs/vue3";
import FollowupsList from '../Partial/Followups/FollowupsList.vue';
import CreateFollowupForm from '../Partial/Followups/CreateFollowupForm.vue';
import FileUploader from '@/Components/FileUploader.vue';

const { props } = usePage();

const user = props.auth.user;

const { hasPermission } = usePermission();
const { hasRole } = usePermission();

const canCreateSolutionFollowups = hasPermission('create solution followups');
const isAdmin = hasRole('admin');
const canDeleteFollowups = hasPermission('delete followups');

const ticketProps = defineProps({
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
    allFiles: {
        type: Array,
        default: () => []
    },
    mode: {
        type: String,
        default: 'create'
    }
});

const form = useForm({
    title: ticketProps.ticket?.title || '',
    description: ticketProps.ticket?.description || '',
    status: ticketProps.ticket?.status || 'open',
    priority: ticketProps.ticket?.priority || 'medium',
    created_at: ticketProps.ticket?.created_at || '',
    updated_at: ticketProps.ticket?.updated_at || '',
    files: [],
});

const followupForm = useForm({
    content: '',
    type: 'comment',
    ticket_id: ticketProps.ticket?.id || null,
});

const isEdit = computed(() => ticketProps.mode === 'edit');
const isCreate = computed(() => ticketProps.mode === 'create');
const isShow = computed(() => ticketProps.mode === 'show');
const isDisabled = computed(() => ticketProps.mode === 'show');

const fileInput = ref(null);

const allFiles = ref(props.allFiles || []);

watch(() => props.allFiles, (newFiles) => {
    allFiles.value = newFiles;
}, { deep: true });

function handleFileChange(event) {
    form.files = event.target.files;
    form.clearErrors('files');
    Object.keys(form.errors).forEach(key => {
        if (key.startsWith('files.')) {
            form.clearErrors(key);
        }
    });
}

const save = () => {
    if (isEdit.value) {
        form.put(route('ticket.update', ticketProps.ticket.id), {
            onSuccess: () => alert('Ticket updated successfully'),
        });
    } else if (isCreate.value) {
        form.post(route('ticket.store'), {
            onSuccess: () => {
                alert('Ticket created successfully');
                form.reset();
                if (fileInput.value) {
                    fileInput.value.value = '';
                }
            },
            onError: (errors) => {
                alert('Failed to create ticket. Please check the form.');
                console.error('Ticket creation errors:', errors);
            }
        });
    }
};

const deleteFollowupForm = useForm({});

const formatDate = (timestamp) => {
    if (!timestamp) return '';
    return new Date(timestamp).toLocaleString();
};

const createdDate = computed(() => formatDate(ticketProps.ticket?.created_at));
const updatedDate = computed(() => formatDate(ticketProps.ticket?.updated_at));

const goBack = () => {
    router.visit(route('ticket.index'));
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const handleFollowupDelete = (followupId) => {
    deleteFollowupForm.delete(route("ticket.followups.destroy", { id: followupId }), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Follow-up deleted successfully');
            const index = ticketProps.ticket.followups.findIndex(f => f.id === followupId);
            if (index !== -1) {
                ticketProps.ticket.followups.splice(index, 1);
            }
        }
    });
};

const handleFollowupUpdate = (form) => {
    form.put(route('ticket.followups.update', form.followup_id), {
        onSuccess: (response) => {
            const updatedFollowup = response.props.ticket.followups.find(f => f.id === form.followup_id);
            const index = ticketProps.ticket.followups.findIndex(f => f.id === updatedFollowup.id);
            if (index !== -1) {
                ticketProps.ticket.followups.splice(index, 1, updatedFollowup);
            }
            alert('Follow-up updated successfully');
        }
    });
};

const handleFollowupCreated = (newFollowup) => {
    ticketProps.ticket.followups.push(newFollowup);
    alert('Follow-up added successfully');
};

const handleFilesUpdated = (updatedFiles) => {
    allFiles.value = updatedFiles;
};
</script>

<template>
    <div class="max-w-2xl mx-auto p-4 sm:px-6 md:max-w-full md:space-x-2 lg:px-8">
        <div class="flex mb-10">
            <button 
                @click="goBack" 
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </button>
        </div>
        <div class="grid md:grid-cols-3 h-full gap-4 sm:gap-6 md:gap-8">
            <!-- Ticket form -->
            <form @submit.prevent="save" class="w-full min-h-full md:col-span-1">
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium"
                        :class="{ 'text-gray-500': isShow, 'text-gray-700': !isShow }">Title</label>
                    <input v-model="form.title" id="title" type="text" :disabled="isDisabled" :class="[
                        'block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1',
                        { 'cursor-not-allowed text-gray-500 bg-gray-200': isDisabled }
                    ]" />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium"
                        :class="{ 'text-gray-500': isShow, 'text-gray-700': !isShow }">Description</label>
                    <textarea v-model="form.description" id="description" :disabled="isDisabled" :class="[
                        'block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm mt-1',
                        { 'cursor-not-allowed text-gray-500 bg-gray-200': isDisabled }
                    ]" rows="4"></textarea>
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

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

                <div class="mb-4" v-if="isCreate">
                    <FileUploader
                        v-model="form.files"
                        :errors="form.errors"
                        fieldName="files"
                        :input-ref="fileInput"
                        @file-change="handleFileChange"
                    />
                </div>

                <div class="mb-4" v-if="(isEdit || isShow) && allFiles.length > 0">
                    <h3 class="block text-sm font-medium text-gray-700 mb-2">Attached Files</h3>
                    <ul class="list-none p-0 m-0 border border-gray-300 rounded-md divide-y divide-gray-300">
                        <li v-for="file in allFiles" :key="`${file.is_followup ? 'followup' : 'ticket'}-${file.id}`" 
                            class="p-3 flex justify-between items-center text-sm">
                            <div class="flex items-center overflow-hidden mr-2">
                                <i class="pi pi-file mr-2 text-gray-500"></i>
                                <a :href="file.is_followup 
                                        ? route('ticket.followups.files.download', { file_id: file.id })
                                        : route('ticket.files.download', { file_id: file.id })"
                                   class="text-indigo-600 hover:text-indigo-800 hover:underline truncate"
                                   :title="file.original_filename">
                                    {{ file.original_filename }}
                                    <span v-if="file.is_followup" class="text-gray-500 text-xs">
                                        (from follow-up on {{ formatDate(file.followup_created_at) }})
                                    </span>
                                </a>
                            </div>
                            <div class="flex-shrink-0 text-gray-500 ml-auto pl-2">
                                 ({{ formatFileSize(file.size) }})
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="mb-4 text-sm text-gray-500" v-else-if="isEdit || isShow">
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
                        @click="form.reset(); form.clearErrors()" 
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

            <div class="md:col-span-2">
                <div v-if="!isCreate">
                    <FollowupsList
                        :followups="ticketProps.ticket.followups"
                        :is-edit="isEdit"
                        :current-user="user"
                        :ticket-id="ticketProps.ticket.id"
                        @followup-deleted="handleFollowupDelete"
                        @followup-updated="handleFollowupUpdate"
                    />
                    
                    <CreateFollowupForm
                        v-if="isEdit"
                        :ticket-id="ticketProps.ticket.id"
                        :can-create-solution="canCreateSolutionFollowups"
                        @created="handleFollowupCreated"
                        @files-updated="handleFilesUpdated"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media (max-width: 768px) {
    form {
        margin-bottom: 2rem;
    }
}

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
