<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Select from 'primevue/select';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({})
    },
    isAdmin: {
        type: Boolean,
        default: false
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
const user_name = ref(props.filters.user_name || '');

// Set initial status based on props
if (props.filters.status) {
    status.value = statusOptions.find(option => option.value === props.filters.status) || null;
}

// Set initial priority based on props
if (props.filters.priority) {
    priority.value = priorityOptions.find(option => option.value === props.filters.priority) || null;
}

const emitChanges = () => {
    const filters = {
        id: id.value,
        title: title.value,
        description: description.value,
        status: status.value ? status.value.value : null,
        priority: priority.value ? priority.value.value : null
    };
    
    // Добавляем фильтрацию по имени пользователя только для админов
    if (props.isAdmin) {
        filters.user_name = user_name.value;
    }
    
    emit('filter-change', filters);
};

const resetFilters = () => {
    id.value = '';
    title.value = '';
    description.value = '';
    status.value = null;
    priority.value = null;
    if (props.isAdmin) {
        user_name.value = '';
    }
    emit('reset-filters');
};

onMounted(() => {
    if (props.isAdmin) {
        document.documentElement.classList.add('admin-active');
    }
});

onUnmounted(() => {
    document.documentElement.classList.remove('admin-active');
});
</script>

<template>
    <div class="filter-container px-2 py-4 mb-4">
        <div class="grid-filter-container">
            <div class="id-cell">
                <InputText v-model="id" placeholder="ID" class="w-full" @input="emitChanges" />
            </div>
            
            <div v-if="isAdmin" class="user-cell">
                <InputText v-model="user_name" placeholder="User" class="w-full" @input="emitChanges" />
            </div>
            
            <div class="title-cell">
                <InputText v-model="title" placeholder="Title" class="w-full" @input="emitChanges" />
            </div>
            
            <div class="description-cell">
                <InputText v-model="description" placeholder="Description" class="w-full" @input="emitChanges" />
            </div>
            
            <div class="status-cell">
                <Select 
                    v-model="status" 
                    :options="statusOptions" 
                    optionLabel="label" 
                    placeholder="Status" 
                    class="w-full" 
                    @change="emitChanges"
                />
            </div>
            
            <div class="priority-cell">
                <Select 
                    v-model="priority" 
                    :options="priorityOptions" 
                    optionLabel="label" 
                    placeholder="Priority" 
                    class="w-full" 
                    @change="emitChanges"
                />
            </div>
            
            <div class="actions-cell">
                <Button 
                    icon="pi pi-filter-slash" 
                    severity="secondary" 
                    outlined 
                    @click="resetFilters" 
                    tooltip="Reset filters" 
                    :tooltipOptions="{ position: 'top' }"
                    class="w-full"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.filter-container {
    background-color: white;
    border: none;
}

.grid-filter-container {
    display: grid;
    grid-template-columns: 5% 10% 12% 33% 12% 12% 10%;
    gap: 8px;
    align-items: center;
}

/* Если админ не активирован, скрываем колонку user */
:root:not(.admin-active) .grid-filter-container {
    grid-template-columns: 5% 0% 12% 43% 12% 12% 10%;
}

.id-cell { grid-column: 1; }
.user-cell { grid-column: 2; }
.title-cell { grid-column: 3; }
.description-cell { grid-column: 4; }
.status-cell { grid-column: 5; }
.priority-cell { grid-column: 6; }
.actions-cell { grid-column: 7; }

/* Стили для инпутов, чтобы они выглядели единообразно */
:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-button) {
    height: 2.5rem;
}

/* Адаптивная верстка для мобильных устройств */
@media (max-width: 768px) {
    .grid-filter-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .id-cell, .user-cell, .title-cell, .description-cell,
    .status-cell, .priority-cell, .actions-cell {
        width: 100%;
    }
}
</style>
