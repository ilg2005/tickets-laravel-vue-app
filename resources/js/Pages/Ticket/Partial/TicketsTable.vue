<script setup>
import { Link } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

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

// Function to get the Tailwind CSS color class for the priority
const getPriorityColorClass = (priority) => {
    switch (priority) {
        case "low":
            return "bg-green-500";
        case "medium":
            return "bg-yellow-500";
        case "high":
            return "bg-red-500";
        default:
            return "bg-gray-500";
    }
};

// Function to get the Tailwind CSS color class for the status
const getStatusColorClass = (status) => {
    switch (status) {
        case "open":
            return "text-green-600 bg-green-100 px-2 py-1 rounded";
        case "in_progress":
            return "text-blue-600 bg-blue-100 px-2 py-1 rounded";
        case "closed":
            return "text-red-600 bg-red-100 px-2 py-1 rounded";
        default:
            return "text-gray-600 bg-gray-100 px-2 py-1 rounded";
    }
};
</script>

<template>
    <div class="table-outer-container">
        <div class="table-container">
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
            <div
                v-for="(ticket, index) in tickets.data"
                :key="ticket.id"
                class="grid grid-filter-container text-sm text-gray-500 hover:bg-gray-100 transition-colors duration-150"
                :class="{ 'bg-gray-50': index % 2 !== 0 }"
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
                    <span
                        :class="getStatusColorClass(ticket.status)"
                    >
                        {{ ticket.status.replace('_', ' ') }}
                    </span>
                </div>
                <div class="priority-cell flex items-center">
                    <div class="flex items-center">
                        <span 
                            :class="[getPriorityColorClass(ticket.priority), 'inline-block w-3 h-3 rounded-full mr-2']"
                        ></span>
                        <span class="text-gray-700">{{ ticket.priority }}</span>
                    </div>
                </div>
                <div class="actions-cell">
                    <div class="flex space-x-2">
                        <Link
                            :href="route('ticket.show', ticket.id)"
                            class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
                            title="View"
                        >
                            <font-awesome-icon
                                icon="fa-solid fa-eye"
                                class="h-4 w-4 opacity-70"
                            />
                        </Link>
                        <Link
                            :href="route('ticket.edit', ticket.id)"
                            class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
                            title="Edit"
                        >
                            <font-awesome-icon
                                icon="fa-solid fa-pen-to-square"
                                class="h-4 w-4 opacity-70"
                            />
                        </Link>
                        <button
                            @click="() => deleteTicket(ticket.id)"
                            class="p-2 text-gray-500 hover:text-gray-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
                            title="Delete"
                        >
                            <font-awesome-icon
                                icon="fa-solid fa-trash-can"
                                class="h-4 w-4 opacity-70"
                            />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Стили для таблицы и фильтров */
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

/* Скрываем полосу прокрутки только в Firefox */
@-moz-document url-prefix() {
    .table-outer-container {
        scrollbar-width: thin;
        scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
    }
}

/* Стилизуем полосу прокрутки для Webkit браузеров (Chrome, Safari) */
.table-outer-container::-webkit-scrollbar {
    height: 6px;
}

.table-outer-container::-webkit-scrollbar-track {
    background-color: #f9fafb;
    border-radius: 3px;
}

.table-outer-container::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.grid-filter-container {
    display: grid;
    grid-template-columns: 5% 10% 12% 33% 12% 12% 10%;
    gap: 0;
    align-items: center;
    width: 100%;
    box-sizing: border-box;
    min-width: 800px;
}

/* Если админ не активирован, скрываем колонку user */
:root:not(.admin-active) .grid-filter-container {
    grid-template-columns: 5% 0% 12% 43% 12% 12% 10%;
}

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

.table-container {
    width: 100%;
    padding: 0;
    box-sizing: border-box;
    min-width: 0;
}

/* Адаптивные стили */
@media (max-width: 768px) {
    .grid {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}
</style> 