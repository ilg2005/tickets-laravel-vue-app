<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head, Link, usePage } from '@inertiajs/vue3';
import CreateTicketForm from './CreateTicketForm.vue';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { usePermission } from '@/Composables/permissions.js';

const confirm = useConfirm();
const toast = useToast();

const { props } = usePage();
const { flash } = props;

defineProps(['tickets']);

const form = useForm({
    message: '',
});

const deleteConfirm = (id) => {
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
            form.delete(route("tickets.destroy", { id: id }), {
                preserveScroll: true,
                onSuccess: () => toast.add({ severity: 'success', summary: 'Success', detail: 'Ticket deleted', life: 3000 }),
            });
        },
    });
};

const { hasRole } = usePermission();
const isUser = hasRole('user');

// Function to get the color for the status
const getStatusColor = (status) => {
    switch (status) {
        case 'open':
            return 'success';
        case 'in_progress':
            return 'info';
        case 'closed':
            return 'danger';
        default:
            return 'secondary';
    }
};

// Function to get the Tailwind CSS color class for the priority
const getPriorityColorClass = (priority) => {
    switch (priority) {
        case 'low':
            return 'text-green-400';
        case 'medium':
            return 'text-yellow-400';
        case 'high':
            return 'text-red-400';
        default:
            return 'text-gray-400';
    }
};
</script>

<template>
    <Toast />
    <ConfirmDialog></ConfirmDialog>

    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tickets</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <DataTable :value="tickets" stripedRows paginator :rows="5" :rowsPerPageOptions="[5, 10, 20, 50]"
                        tableStyle="min-width: 50rem"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}">

                        <template #header>
                            <div class="flex flex-wrap items-center justify-end gap-2 min-h-8">
                                <Link v-if="isUser" :href="route('tickets.create')">
                                    <Button label="Add Ticket" icon="pi pi-plus-circle" rounded link />
                                </Link>
                            </div>
                        </template>

                        <Column field="id" header="#"></Column>
                        <Column field="title" header="Title"></Column>
                        <Column field="description" header="Description"></Column>

                        <Column field="status" header="Status">
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.status" :severity="getStatusColor(slotProps.data.status)" />
                            </template>
                        </Column>

                        <Column field="priority" header="Priority">
                            <template #body="slotProps">
                                <span class="inline-flex items-center">
                                    <i  class="pi pi-circle-on" :class="getPriorityColorClass(slotProps.data.priority)" style="margin-right: 0.5rem; font-size: 0.7rem"></i>
                                    {{ slotProps.data.priority }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Actions">
                            <template #body="slotProps">
                                <div class="flex">
                                    <Link :href="route('tickets.show', slotProps.data.id)">
                                        <Button label="" icon="pi pi-eye" text severity="secondary" />
                                    </Link>

                                    <Link :href="route('tickets.edit', slotProps.data.id)">
                                        <Button label="" icon="pi pi-pencil" text severity="secondary" />
                                    </Link>

                                    <Button
                                        label=""
                                        @click="() => deleteConfirm(slotProps.data.id)"
                                        icon="pi pi-trash" text
                                        severity="secondary" />
                                </div>
                            </template>
                        </Column>
                    </DataTable>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
