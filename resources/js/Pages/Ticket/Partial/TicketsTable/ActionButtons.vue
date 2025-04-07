<script setup>
import { Link } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
    ticketId: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['delete']);

const deleteTicket = () => {
    emit('delete', props.ticketId);
};
</script>

<template>
    <div class="flex space-x-2">
        <Link
            :href="route('ticket.show', ticketId)"
            class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
            title="View"
        >
            <font-awesome-icon
                icon="fa-solid fa-eye"
                class="h-4 w-4 opacity-70"
            />
        </Link>
        <Link
            :href="route('ticket.edit', ticketId)"
            class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
            title="Edit"
        >
            <font-awesome-icon
                icon="fa-solid fa-pen-to-square"
                class="h-4 w-4 opacity-70"
            />
        </Link>
        <button
            @click="deleteTicket"
            class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
            title="Delete"
        >
            <font-awesome-icon
                icon="fa-solid fa-trash-can"
                class="h-4 w-4 opacity-70"
            />
        </button>
    </div>
</template> 