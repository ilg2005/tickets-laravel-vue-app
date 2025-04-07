<script setup>
import TableHeader from './TableHeader.vue';
import TableRow from './TableRow.vue';
import './TableStyles.css';

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