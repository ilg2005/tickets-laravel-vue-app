<script setup>
import TableHeader from './TableHeader.vue';
import TableRow from './TableRow.vue';

const props = defineProps({
    tickets: {
        type: Object,
        required: true
    },
    sort: {
        type: Object,
        required: true
    },
    isAdmin: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['sort', 'delete']);

const onSort = (field) => {
    emit('sort', field);
};

const deleteTicket = (id) => {
    emit('delete', id);
};
</script>

<template>
    <div class="table-outer-container">
        <div class="table-container">
            
            <!-- Шапка таблицы -->
            <TableHeader 
                :sort="sort" 
                :is-admin="isAdmin" 
                @sort="onSort" 
            />
            
            <!-- Строки таблицы для каждого тикета -->
            <TableRow 
                v-for="(ticket, index) in tickets.data" 
                :key="ticket.id" 
                :ticket="ticket" 
                :is-admin="isAdmin" 
                :is-even="index % 2 === 0"
                @delete="deleteTicket" 
            />
        </div>
    </div>
</template>

<style scoped>
.table-outer-container {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding: 0;
    box-sizing: border-box;
    scrollbar-width: thin;
    margin-bottom: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
}

.table-container {
    width: 100%;
    padding: 0;
    box-sizing: border-box;
    min-width: 0;
}

.table-outer-container::-webkit-scrollbar-track {
    background-color: #f9fafb;
    border-radius: 3px;
}

.table-outer-container::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}
</style> 