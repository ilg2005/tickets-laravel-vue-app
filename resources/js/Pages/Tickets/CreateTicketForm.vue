<script setup>
import { useForm, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { computed, ref } from 'vue';
import { useToast } from "primevue/usetoast";
import { usePermission } from '@/Composables/permissions.js';
import { useConfirm } from "primevue/useconfirm";
import { usePage } from "@inertiajs/vue3";

const { props } = usePage()

const user = props.auth.user;

const { hasPermission } = usePermission();
const { hasRole } = usePermission();

const canCreateSolutionFollowups = hasPermission('create solution followups');
const isAdmin = hasRole('admin');
const canDeleteFollowups = hasPermission('delete followups');

const confirm = useConfirm();
const toast = useToast();

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
        form.put(route('tickets.update', ticketProps.ticket.id), {
            onSuccess: () =>
                toast.add({ severity: 'success', summary: 'Success', detail: 'Ticket updated', life: 3000 }),
        });
    } else if (isCreate.value) {
        form.post(route('tickets.store'), {
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Success', detail: 'Ticket created', life: 3000 });
                form.reset();
                if (fileInput.value) {
                    fileInput.value.value = '';
                }
            },
            onError: (errors) => {
                toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to create ticket. Please check the form.', life: 3000 });
                console.error('Ticket creation errors:', errors);
            }
        });
    }
};

const saveFollowUps = () => {
    followupForm.post(route('followups.store'), {
        onSuccess: (response) => {
            const message = response.props.flash.success || 'Follow-up added';
            toast.add({ severity: 'success', summary: 'Success', detail: message, life: 3000 });
            const newFollowup = response.props.ticket.followups[response.props.ticket.followups.length - 1];
            if (ticketProps.ticket) {
                ticketProps.ticket.followups.push(newFollowup);
            }
            followupForm.reset();
        },
        onError: (errors) => {
            const message = errors[0] || 'Error adding follow-up';
            toast.add({ severity: 'error', summary: 'Error', detail: message, life: 3000 });
        },
    });
};

const editFollowupForm = useForm({
    content: '',
    type: 'comment',
    followup_id: null,
    ticket_id: ticketProps.ticket?.id || null,
});

const deleteFollowupForm = useForm({});

const isEditingFollowup = ref(null);

const startEditingFollowup = (followup) => {
    editFollowupForm.content = followup.content;
    editFollowupForm.type = followup.type;
    editFollowupForm.followup_id = followup.id;
    isEditingFollowup.value = followup.id;
};

const cancelEditingFollowup = () => {
    isEditingFollowup.value = null;
    editFollowupForm.reset();
    editFollowupForm.clearErrors();
};

const saveEditedFollowup = () => {
    editFollowupForm.put(route('followups.update', editFollowupForm.followup_id), {
        onSuccess: (response) => {
            const message = response.props.flash.success || 'Follow-up updated';
            toast.add({ severity: 'success', summary: 'Success', detail: message, life: 3000 });
            const updatedFollowup = response.props.ticket.followups.find(f => f.id === editFollowupForm.followup_id)
            const index = ticketProps.ticket.followups.findIndex(f => f.id === updatedFollowup.id);
            if (index !== -1) {
                ticketProps.ticket.followups.splice(index, 1, updatedFollowup);
            }
            cancelEditingFollowup();
        },
        onError: (errors) => {
            const message = errors[0] || 'Error updating follow-up';
            toast.add({ severity: 'error', summary: 'Error', detail: message, life: 3000 });
        },
    });
};

const formatDate = (timestamp) => {
    if (!timestamp) return '';
    return new Date(timestamp).toLocaleString();
};

const createdDate = computed(() => formatDate(ticketProps.ticket?.created_at));
const updatedDate = computed(() => formatDate(ticketProps.ticket?.updated_at));

const goBack = () => {
    router.visit(route('tickets.index'));
};

const deleteConfirm = (followupId) => {
    confirm.require({
        message: 'Are you sure you want to proceed?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        rejectProps: {
            label: 'Cancel',
            severity: 'secondary',
            outlined: true
        },
        acceptProps: {
            label: 'Confirm'
        },
        accept: () => {
            deleteFollowupForm.delete(route("followups.destroy", { id: followupId }), {
                preserveScroll: true,
                onSuccess: () => {
                    toast.add({ severity: 'success', summary: 'Success', detail: 'Follow-up deleted', life: 3000 });
                    const index = ticketProps.ticket.followups.findIndex(f => f.id === followupId);
                    if (index !== -1) {
                        ticketProps.ticket.followups.splice(index, 1);
                    }
                },
                onError: (errors) => {
                    const message = errors[0] || 'Error deleting follow-up';
                    toast.add({ severity: 'error', summary: 'Error', detail: message, life: 3000 });
                },
            });
        },
    });
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <Toast />
    <ConfirmDialog></ConfirmDialog>

    <div class="max-w-2xl mx-auto p-4 sm:px-6 md:max-w-full md:space-x-2 lg:px-8">
        <div class="flex mb-10">
            <Button label="Back" severity="secondary" @click="goBack" text icon="pi pi-arrow-left" />
        </div>
        <div class="grid md:grid-cols-3 h-full gap-8">
            <!-- Ticket form -->
            <form @submit.prevent="save" class="w-full min-h-full">
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
                    <label for="files" class="block text-sm font-medium text-gray-700">Прикрепить файлы</label>
                    <p class="text-xs text-gray-500 mb-1">Макс. 10MB на файл. Разрешены: jpg, png, pdf, doc(x), zip, rar, txt</p>
                    <input
                        id="files"
                        ref="fileInput"
                        type="file"
                        multiple
                        @change="handleFileChange"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 border border-gray-300 rounded-md shadow-sm cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <div v-if="form.files && form.files.length > 0" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <p>Выбранные файлы:</p>
                        <ul>
                            <li v-for="file in Array.from(form.files)" :key="file.name">
                                {{ file.name }} ({{ (file.size / 1024).toFixed(2) }} KB)
                            </li>
                        </ul>
                    </div>
                    <InputError class="mt-2" :message="form.errors.files" />
                    <div v-for="(error, index) in form.errors" :key="index">
                        <InputError v-if="index.startsWith('files.')" class="mt-1" :message="error" />
                    </div>
                </div>

                <div class="mb-4" v-if="(isEdit || isShow) && ticketProps.ticket?.files?.length > 0">
                    <h3 class="block text-sm font-medium text-gray-700 mb-2">Прикрепленные файлы</h3>
                    <ul class="list-none p-0 m-0 border border-gray-300 rounded-md divide-y divide-gray-300">
                        <li v-for="file in ticketProps.ticket.files" :key="file.id" class="p-3 flex justify-between items-center text-sm">
                            <div class="flex items-center overflow-hidden mr-2">
                                <i class="pi pi-file mr-2 text-gray-500"></i>
                                <a :href="route('tickets.files.download', { ticket: ticketProps.ticket.id, ticket_file: file.id })"
                                   class="text-indigo-600 hover:text-indigo-800 hover:underline truncate"
                                   :title="file.original_filename">
                                    {{ file.original_filename }}
                                </a>
                            </div>
                            <div class="flex-shrink-0 text-gray-500 ml-auto pl-2">
                                 ({{ formatFileSize(file.size) }})
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="mb-4 text-sm text-gray-500" v-else-if="isEdit || isShow">
                    Нет прикрепленных файлов.
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

                <div v-if="isEdit || isCreate" class="flex justify-end gap-2 mt-4">
                    <Button label="Cancel" severity="secondary" type="button" @click="form.reset(); form.clearErrors()" :disabled="form.processing"/>
                    <Button :label="isEdit ? 'Update Ticket' : 'Create Ticket'" severity="primary" type="submit" :loading="form.processing" />
                </div>
            </form>

            <div class="col-span-2">
                <div class="" v-if="!isCreate">
                    <h2 class="text-lg font-semibold mb-4">Followups</h2>
                    <ul>
                        <ScrollPanel class="" style="width: 100%;" :dt="{
                            // bar: {
                            //     background: '{primary.color}'
                            // }
                        }">
                            <template v-if="ticketProps.ticket.followups.length > 0">
                                <li v-for="followup in ticketProps.ticket.followups" :key="followup.id" class="mb-4">
                                    <div class="bg-white shadow rounded-lg p-4">
                                        <div class="flex justify-between ">
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{ followup.user.name }}</span>

                                                <div>
                                                    <!-- {{ followup.id }} -->
                                                    <span class="text-sm text-gray-500">{{
                                                        formatDate(followup.created_at)
                                                        }}</span>

                                                    <span v-if="followup.created_at !== followup.updated_at"
                                                        class="text-sm text-gray-400"> - edited</span>

                                                    <!-- <span class="text-sm text-gray-500">{{
                                                    (formatDate(followup.updated_at))
                                                }}</span> -->
                                                </div>

                                            </div>
                                            <div class="">
                                                <Tag :style="{ backgroundColor: followup.type === 'comment' ? '#FFBF00' : '#1E90FF' }"
                                                    style="color: white">
                                                    {{ followup.type }}
                                                </Tag>
                                                <div class="flex justify-end">
                                                    <Button v-if="isEdit && (followup.user_id === user.id)"
                                                        icon="pi pi-pencil" text size="small" severity="secondary"
                                                        @click="startEditingFollowup(followup)" />
                                                    <Button
                                                        v-if="(isEdit && isAdmin && canDeleteFollowups) || isEdit && (followup.user_id === user.id)"
                                                        icon="pi pi-trash" text size="small" severity="danger"
                                                        @click="() => deleteConfirm(followup.id)" />
                                                </div>
                                            </div>
                                        </div>
                                        <div v-if="isEditingFollowup === followup.id">
                                            <textarea v-model="editFollowupForm.content"
                                                class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                                rows="4"></textarea>
                                            <InputError :message="editFollowupForm.errors.content" class="mt-2" />
                                            <div class="flex justify-end gap-2 mt-2">
                                                <Button label="Cancel" severity="secondary"
                                                    @click="cancelEditingFollowup" />
                                                <Button label="Save" severity="primary" @click="saveEditedFollowup" />
                                            </div>
                                        </div>
                                        <p v-else class="mt-2 text-gray-700">{{ followup.content }}</p>
                                    </div>
                                </li>
                            </template>
                            <template v-else>
                                <div class="bg-white shadow rounded-lg p-4 text-center">
                                    <p class="text-gray-500">No followups</p>
                                </div>
                            </template>
                        </ScrollPanel>
                    </ul>
                </div>

                <form @submit.prevent="saveFollowUps" class="w-full" v-if="isEdit">
                    <div class="gap-4 p-4 grid grid-cols-3">
                        <div class="mb-4 col-span-2">
                            <label for="followup-content" class="block text-sm font-medium">Follow-up Content</label>
                            <textarea v-model="followupForm.content" id="followup-content" :class="[
                                'block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'
                            ]" rows="6"></textarea>
                            <InputError :message="followupForm.errors.content" class="mt-2" />
                        </div>

                        <div class="">
                            <div class="mb-4">
                                <label for="followup-type" class="block text-sm font-medium">Follow-up Type</label>
                                <select :disabled="!canCreateSolutionFollowups" v-model="followupForm.type"
                                    id="followup-type" :class="[
                                        'block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm'
                                    ]">
                                    <option value="comment">Comment</option>
                                    <option value="solution">Solution</option>
                                </select>
                                <InputError :message="followupForm.errors.type" class="mt-2" />
                            </div>
                            <div class="flex justify-end gap-2">
                                <Button label="Cancel" severity="secondary" class="mt-4" type="button"
                                    @click="followupForm.reset(); followupForm.clearErrors()" />
                                <Button severity="primary" class="mt-4" type="submit">Add Follow-up</Button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
