<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import InputText from '@/Components/InputText.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

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
    <div class="filter-container">
        <div class="grid-filter-container">
            <div class="id-cell">
                <label for="filter-id" class="block text-xs font-medium text-gray-500 mb-1 md:hidden">#</label>
                <InputText id="filter-id" v-model="id" placeholder="ID" class="w-full" @input="emitChanges" />
            </div>
            
            <div v-if="isAdmin" class="user-cell">
                <label for="filter-user" class="block text-xs font-medium text-gray-500 mb-1 md:hidden">User</label>
                <InputText id="filter-user" v-model="user_name" placeholder="User" class="w-full" @input="emitChanges" />
            </div>
            
            <div class="title-cell">
                <label for="filter-title" class="block text-xs font-medium text-gray-500 mb-1 md:hidden">Title</label>
                <InputText id="filter-title" v-model="title" placeholder="Title" class="w-full" @input="emitChanges" />
            </div>
            
            <div class="description-cell">
                <label for="filter-desc" class="block text-xs font-medium text-gray-500 mb-1 md:hidden">Description</label>
                <InputText id="filter-desc" v-model="description" placeholder="Description" class="w-full" @input="emitChanges" />
            </div>
            
            <div class="status-cell">
                <label for="filter-status" class="block text-xs font-medium text-gray-500 mb-1 md:hidden">Status</label>
                <select id="filter-status" v-model="status" @change="emitChanges" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="" disabled>Status</option>
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
            
            <div class="priority-cell">
                <label for="filter-priority" class="block text-xs font-medium text-gray-500 mb-1 md:hidden">Priority</label>
                <select id="filter-priority" v-model="priority" @change="emitChanges" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="" disabled>Priority</option>
                    <option v-for="option in priorityOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
            
            <div class="actions-cell">                
                <button @click="resetFilters" class="reset-button">
                    <font-awesome-icon icon="fa-solid fa-rotate-left" />
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.filter-container {
    background-color: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    width: 100%;
    max-width: 100%;
    padding: 0.75rem 0.75rem 0.75rem 0.5rem;
    margin-bottom: 1rem;
    box-sizing: border-box;
}

.grid-filter-container {
    display: grid;
    grid-template-columns: 5% 10% 12% 33% 12% 12% 10%;
    gap: 8px;
    align-items: center;
    width: 100%;
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
.actions-cell { 
    grid-column: 7;
    display: flex;
    justify-content: center;
}

.actions-cell button {
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Стили для инпутов, чтобы они выглядели единообразно */
:deep(.p-inputtext),
:deep(.p-dropdown),
:deep(.p-button) {
    height: 2.5rem;
}

/* Адаптивность для планшетов */
@media (max-width: 1024px) {
    .grid-filter-container {
        grid-template-columns: 1fr 1fr 1fr;
    }
    
    :root:not(.admin-active) .grid-filter-container {
        grid-template-columns: 1fr 1fr 1fr;
    }
    
    .id-cell { grid-column: 1; grid-row: 1; }
    .user-cell { grid-column: 2; grid-row: 1; }
    .title-cell { grid-column: 3; grid-row: 1; }
    .description-cell { grid-column: 1; grid-row: 2; }
    .status-cell { grid-column: 2; grid-row: 2; }
    .priority-cell { grid-column: 3; grid-row: 2; }
    .actions-cell { 
        grid-column: 1; 
        grid-row: 3; 
        justify-content: flex-start;
    }
    
    .actions-cell button {
        width: auto;
        padding: 0.5rem 1rem;
    }
}

/* Адаптивность для мобильных устройств */
@media (max-width: 640px) {
    .grid-filter-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .id-cell, .user-cell, .title-cell, .description-cell,
    .status-cell, .priority-cell, .actions-cell {
        width: 100%;
    }
    
    .actions-cell {
        justify-content: flex-start;
    }
    
    .actions-cell button {
        width: 100%;
    }
}

.reset-button {
    width: 38px;
    height: 38px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    color: #6b7280;
    transition: all 0.15s ease-in-out;
}

.reset-button:hover {
    background-color: #e5e7eb;
    color: #4b5563;
}

/* Иконка внутри кнопки */
.reset-button .svg-inline--fa {
    width: 1rem;
    height: 1rem;
}

@media (max-width: 1024px) {
    .reset-button {
        width: auto;
        padding: 0.5rem 1rem;
    }
    
    .reset-button .svg-inline--fa {
        width: 1rem;
        height: 1rem;
        margin-right: 0;
    }
}

@media (max-width: 640px) {
    .reset-button {
        width: 100%;
        padding: 0.5rem;
    }
}
</style>
