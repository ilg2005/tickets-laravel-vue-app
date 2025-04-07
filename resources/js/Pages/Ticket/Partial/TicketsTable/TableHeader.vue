<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';


const props = defineProps({
    sort: {
        type: Object,
        required: true
    },
    isAdmin: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['sort']);

const onSort = (field) => {
    emit('sort', field);
};
</script>

<template>
    <div
        class="grid grid-filter-container text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100"
    >
        <div
            @click="onSort('id')"
            class="id-cell cursor-pointer flex items-center"
        >
            #
            <span class="ml-1">
                <font-awesome-icon
                    v-if="sort.field === 'id'"
                    :icon="sort.order === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'"
                    class="h-3 w-3"
                />
                <font-awesome-icon
                    v-else
                    icon="fa-solid fa-sort"
                    class="h-3 w-3 text-gray-400"
                />
            </span>
        </div>
        <div
            v-if="isAdmin"
            @click="onSort('user_name')"
            class="user-cell cursor-pointer flex items-center"
        >
            User
            <span class="ml-1">
                <font-awesome-icon
                    v-if="sort.field === 'user_name'"
                    :icon="sort.order === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'"
                    class="h-3 w-3"
                />
                <font-awesome-icon
                    v-else
                    icon="fa-solid fa-sort"
                    class="h-3 w-3 text-gray-400"
                />
            </span>
        </div>
        <div
            @click="onSort('title')"
            class="title-cell cursor-pointer flex items-center"
        >
            Title
            <span class="ml-1">
                <font-awesome-icon
                    v-if="sort.field === 'title'"
                    :icon="sort.order === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'"
                    class="h-3 w-3"
                />
                <font-awesome-icon
                    v-else
                    icon="fa-solid fa-sort"
                    class="h-3 w-3 text-gray-400"
                />
            </span>
        </div>
        <div class="description-cell">
            Description
        </div>
        <div
            @click="onSort('status')"
            class="status-cell cursor-pointer flex items-center"
        >
            Status
            <span class="ml-1">
                <font-awesome-icon
                    v-if="sort.field === 'status'"
                    :icon="sort.order === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'"
                    class="h-3 w-3"
                />
                <font-awesome-icon
                    v-else
                    icon="fa-solid fa-sort"
                    class="h-3 w-3 text-gray-400"
                />
            </span>
        </div>
        <div
            @click="onSort('priority')"
            class="priority-cell cursor-pointer flex items-center"
        >
            Priority
            <span class="ml-1">
                <font-awesome-icon
                    v-if="sort.field === 'priority'"
                    :icon="sort.order === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down'"
                    class="h-3 w-3"
                />
                <font-awesome-icon
                    v-else
                    icon="fa-solid fa-sort"
                    class="h-3 w-3 text-gray-400"
                />
            </span>
        </div>
        <div class="actions-cell text-center">Actions</div>
    </div>
</template>

<style scoped>
.grid-filter-container {
    /* Основная структура сетки */
    display: grid;
    grid-template-columns: 5% 10% 12% 33% 12% 12% 10%;
    gap: 0;
    align-items: center;
    
    /* Размеры */
    width: 100%;
    box-sizing: border-box;
    min-width: 800px;
}

/* Модификация сетки когда админ-режим не активен */
:root:not(.admin-active) .grid-filter-container {
    grid-template-columns: 5% 0% 12% 43% 12% 12% 10%;
}

/* Ячейки таблицы */
.id-cell {
    grid-column: 1;
    padding: 0.75rem 0.5rem;
}

.user-cell {
    grid-column: 2;
    padding: 0.75rem 0.5rem;
}

.title-cell {
    grid-column: 3;
    padding: 0.75rem 0.5rem;
}

.description-cell {
    grid-column: 4;
    padding: 0.75rem 0.5rem;
}

.status-cell {
    grid-column: 5;
    padding: 0.75rem 0.125rem;
}

.priority-cell {
    grid-column: 6;
    padding: 0.75rem 0.125rem;
}

.actions-cell {
    grid-column: 7;
    padding: 0.75rem 0.5rem;
}

@media (max-width: 768px) {
    .grid {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}
</style> 