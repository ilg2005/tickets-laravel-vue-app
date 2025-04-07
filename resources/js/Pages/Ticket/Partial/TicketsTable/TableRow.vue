<script setup>
import StatusBadge from './StatusBadge.vue';
import PriorityBadge from './PriorityBadge.vue';
import ActionButtons from './ActionButtons.vue';
import './TableStyles.css';

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