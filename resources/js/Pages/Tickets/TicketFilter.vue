<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import InputText from '@/Components/InputText.vue';
import Button from '@/Components/Button.vue';

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
const status = ref(props.filters.status || '');
const priority = ref(props.filters.priority || '');
const user_name = ref(props.filters.user_name || '');

const emitChanges = () => {
    const filters = {
        id: id.value,
        title: title.value,
        description: description.value,
        status: status.value,
        priority: priority.value
    };
    
    if (props.isAdmin) {
        filters.user_name = user_name.value;
    }
    
    emit('filter-change', filters);
};

const resetFilters = () => {
    id.value = '';
    title.value = '';
    description.value = '';
    status.value = '';
    priority.value = '';
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
                <select v-model="status" @change="emitChanges" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="" disabled>Status</option>
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
            
            <div class="priority-cell">
                <select v-model="priority" @change="emitChanges" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="" disabled>Priority</option>
                    <option v-for="option in priorityOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
            
            <div class="actions-cell">
                <button @click="resetFilters" class="inline-flex justify-center items-center w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-200 transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                    </svg>
                </button>
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
