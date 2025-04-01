<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useForm, Head, Link, usePage, router } from '@inertiajs/vue3';
import CreateTicketForm from './CreateTicketForm.vue';
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";
import { usePermission } from '@/Composables/permissions.js';
import TicketFilter from '@/Pages/Tickets/TicketFilter.vue';
import { ref, computed } from 'vue';

const confirm = useConfirm();
const toast = useToast();

const page = usePage();
const { flash } = page.props;

const props = defineProps({
    tickets: Array,
    filters: {
        type: Object,
        default: () => ({})
    },
    sort: {
        type: Object,
        default: () => ({ field: 'updated_at', order: 'desc' })
    },
    isAdmin: {
        type: Boolean,
        default: false
    }
});

const form = useForm({
    message: '',
});

const filters = ref(props.filters || {});
const sort = ref(props.sort || { field: 'updated_at', order: 'desc' });

const handleFilterChange = (newFilters) => {
    filters.value = newFilters;
    applyFilters();
};

const resetFilters = () => {
    filters.value = {};
    sort.value = { field: 'updated_at', order: 'desc' };
    applyFilters();
};

const onSort = (event) => {
    sort.value = {
        field: event.sortField,
        order: event.sortOrder === 1 ? 'asc' : 'desc'
    };
    applyFilters();
};

const applyFilters = () => {
    router.get(route('tickets.index'), {
        filters: filters.value,
        sort: sort.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <!-- Filter component -->
                    <TicketFilter 
                        :filters="filters" 
                        :is-admin="isAdmin"
                        @filter-change="handleFilterChange" 
                        @reset-filters="resetFilters" 
                    />
                    
                    <DataTable 
                        :value="tickets" 
                        stripedRows 
                        paginator 
                        :rows="5" 
                        :rowsPerPageOptions="[5, 10, 20, 50]"
                        tableStyle="min-width: 50rem; width: 100%;"
                        paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                        currentPageReportTemplate="{first} to {last} of {totalRecords}"
                        :sortField="sort.field"
                        :sortOrder="sort.order === 'asc' ? 1 : -1"
                        @sort="onSort"
                        class="ticket-table"
                    >
                        <template #header>
                            <div class="flex flex-wrap items-center justify-end gap-2 min-h-8">
                                <Link v-if="isUser" :href="route('tickets.create')">
                                    <Button label="Add Ticket" icon="pi pi-plus-circle" rounded link />
                                </Link>
                            </div>
                        </template>

                        <Column field="id" header="#" sortable style="width: 5%"></Column>
                        
                        <!-- User column - только для админов -->
                        <Column v-if="isAdmin" field="user_name" header="User" sortable style="width: 10%">
                            <template #body="slotProps">
                                {{ slotProps.data.user ? slotProps.data.user.name : '-' }}
                            </template>
                        </Column>
                        
                        <Column field="title" header="Title" sortable style="width: 12%"></Column>
                        <Column field="description" header="Description" style="width: 33%"></Column>

                        <Column field="status" header="Status" sortable style="width: 12%">
                            <template #body="slotProps">
                                <Tag :value="slotProps.data.status" :severity="getStatusColor(slotProps.data.status)" />
                            </template>
                        </Column>

                        <Column field="priority" header="Priority" sortable style="width: 12%">
                            <template #body="slotProps">
                                <span class="inline-flex items-center">
                                    <i class="pi pi-circle-on" :class="getPriorityColorClass(slotProps.data.priority)" style="margin-right: 0.5rem; font-size: 0.7rem"></i>
                                    {{ slotProps.data.priority }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Actions" style="width: 10%">
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

<style scoped>
.ticket-table :deep(th), .ticket-table :deep(td) {
    padding: 0.75rem 0.5rem;
}

/* Стили для выпадающих списков в фильтре */
:deep(.filter-dropdown) {
    width: 100%;
    min-width: 120px;
}

:deep(.filter-dropdown .p-dropdown-label) {
    display: block;
    width: 100%;
    text-overflow: clip;
    white-space: normal;
}

/* Полностью показываем текст в выпадающих списках */
:deep(.p-dropdown-panel .p-dropdown-items .p-dropdown-item) {
    white-space: normal;
    word-break: break-word;
}

/* Корректируем ширину выпадающего списка */
:deep(.p-dropdown-panel) {
    min-width: 150px;
}

@media (max-width: 768px) {
    /* Мобильные стили для таблицы */
}
</style>
