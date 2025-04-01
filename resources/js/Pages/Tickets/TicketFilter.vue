<script setup>
import { ref } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({})
    }
});

const emit = defineEmits(['filter-change', 'reset-filters']);

const statusOptions = [
    { label: 'Open', value: 'open' },
    { label: 'In Progress', value: 'in_progress' },
    { label: 'Closed', value: 'closed' }
];

const priorityOptions = [
    { label: 'Low', value: 'low' },
    { label: 'Medium', value: 'medium' },
    { label: 'High', value: 'high' }
];

const id = ref(props.filters.id || '');
const title = ref(props.filters.title || '');
const description = ref(props.filters.description || '');
const status = ref(null);
const priority = ref(null);

// Set initial status based on props
if (props.filters.status) {
    status.value = statusOptions.find(option => option.value === props.filters.status) || null;
}

// Set initial priority based on props
if (props.filters.priority) {
    priority.value = priorityOptions.find(option => option.value === props.filters.priority) || null;
}

const emitChanges = () => {
    emit('filter-change', {
        id: id.value,
        title: title.value,
        description: description.value,
        status: status.value ? status.value.value : null,
        priority: priority.value ? priority.value.value : null
    });
};

const resetFilters = () => {
    id.value = '';
    title.value = '';
    description.value = '';
    status.value = null;
    priority.value = null;
    emit('reset-filters');
};
</script>

<template>
    <div class="filter-container px-0 py-4 mb-4">
        <div class="table-layout">
            <table class="filter-table">
                <thead>
                    <tr>
                        <th class="id-column">
                            <InputText v-model="id" placeholder="ID" class="w-full" @input="emitChanges" />
                        </th>
                        <th class="title-column">
                            <InputText v-model="title" placeholder="Search by title" class="w-full" @input="emitChanges" />
                        </th>
                        <th class="description-column">
                            <InputText v-model="description" placeholder="Search by description" class="w-full" @input="emitChanges" />
                        </th>
                        <th class="status-column">
                            <Select 
                                v-model="status" 
                                :options="statusOptions" 
                                optionLabel="label" 
                                placeholder="Status" 
                                class="w-full" 
                                @change="emitChanges"
                            />
                        </th>
                        <th class="priority-column">
                            <Select 
                                v-model="priority" 
                                :options="priorityOptions" 
                                optionLabel="label" 
                                placeholder="Priority" 
                                class="w-full" 
                                @change="emitChanges"
                            />
                        </th>
                        <th class="actions-column text-center">
                            <Button 
                                icon="pi pi-filter-slash" 
                                severity="secondary" 
                                outlined 
                                @click="resetFilters" 
                                tooltip="Reset filters" 
                                :tooltipOptions="{ position: 'top' }"
                            />
                        </th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</template>

<style scoped>
.filter-container {
    background-color: white;
    border: none;
}

.table-layout {
    width: 100%;
    overflow-x: auto;
}

.filter-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
}

.filter-table th {
    padding: 0 0.5rem;
    font-weight: normal;
}

/* Колонки точно как в таблице данных */
.id-column {
    width: 5%;
    min-width: 50px;
}

.title-column {
    width: 20%;
}

.description-column {
    width: 40%;
}

.status-column, .priority-column {
    width: 12%;
    min-width: 120px;
}

.actions-column {
    width: 11%;
    min-width: 100px;
}

/* Стили для инпутов, чтобы они выглядели единообразно */
:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-button) {
    height: 2.5rem;
}

/* Для мобильных устройств можно скрыть некоторые фильтры или изменить layout */
@media (max-width: 768px) {
    .filter-table {
        display: flex;
        flex-wrap: wrap;
    }
    
    .filter-table thead {
        width: 100%;
    }
    
    .filter-table tr {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
    }
    
    .filter-table th {
        display: block;
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .id-column, .title-column, .description-column,
    .status-column, .priority-column, .actions-column {
        width: 100%;
    }
}
</style>
