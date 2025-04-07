<script setup>
import StatusBadge from './StatusBadge.vue';
import PriorityBadge from './PriorityBadge.vue';
import ActionButtons from './ActionButtons.vue';

const props = defineProps({
    ticket: {
        type: Object,
        required: true
    },
    isAdmin: {
        type: Boolean,
        default: false
    },
    isEven: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['delete']);

const onDelete = (id) => {
    emit('delete', id);
};
</script>

<template>
    <div
        class="grid grid-filter-container text-sm text-gray-500 hover:bg-gray-100 transition-colors duration-150"
        :class="{ 'bg-gray-50': !isEven }"
    >
        <div class="id-cell">{{ ticket.id }}</div>
        <div v-if="isAdmin" class="user-cell">
            {{ ticket.user ? ticket.user.name : "-" }}
        </div>
        <div class="title-cell">
            {{ ticket.title }}
        </div>
        <div class="description-cell">
            {{ ticket.description }}
        </div>
        <div class="status-cell flex items-center">
            <StatusBadge :status="ticket.status" />
        </div>
        <div class="priority-cell flex items-center">
            <PriorityBadge :priority="ticket.priority" />
        </div>
        <div class="actions-cell">
            <ActionButtons 
                :ticket-id="ticket.id" 
                @delete="onDelete"
            />
        </div>
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
</style> 