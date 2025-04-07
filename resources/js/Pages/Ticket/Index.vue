<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import { useForm } from "@inertiajs/vue3";
import CreateTicketForm from "./Partial/CreateTicketForm.vue";
import TicketFilter from "./Partial/TicketFilter.vue";
import { ref, computed } from "vue";
import { usePermission } from "@/Composables/permissions.js";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from '@fortawesome/fontawesome-svg-core';
import { faSort, faSortUp, faSortDown } from '@fortawesome/free-solid-svg-icons';

const page = usePage();

const props = defineProps({
    tickets: Object,
    filters: {
        type: Object,
        default: () => ({}),
    },
    sort: {
        type: Object,
        default: () => ({ field: "updated_at", order: "desc" }),
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    message: "",
});

const filters = ref(props.filters || {});
const sort = ref(props.sort || { field: "updated_at", order: "desc" });
const perPage = ref(15);

const handleFilterChange = (newFilters) => {
    filters.value = newFilters;
    applyFilters();
};

const resetFilters = () => {
    filters.value = {};
    sort.value = { field: "updated_at", order: "desc" };
    applyFilters();
};

const onSort = (field) => {
    sort.value = {
        field: field,
        order: sort.value.order === "asc" ? "desc" : "asc",
    };
    applyFilters();
};

const applyFilters = () => {
    router.get(
        route("ticket.index"),
        {
            filters: filters.value,
            sort: sort.value,
            per_page: perPage.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const changePerPage = () => {
    applyFilters();
};

const deleteConfirm = (id) => {
    if (confirm("Are you sure you want to proceed?")) {
        form.delete(route("ticket.destroy", { id: id }), {
            preserveScroll: true,
            onSuccess: () => alert("Ticket deleted successfully"),
        });
    }
};

const { hasRole } = usePermission();
const isUser = hasRole("user");

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

// Функция для модификации URL пагинации, добавляя параметр per_page
const getModifiedUrl = (url) => {
    if (!url) return null;
    const urlObj = new URL(url);
    urlObj.searchParams.set("per_page", perPage.value);
    return urlObj.href;
};

// Добавляю иконки сортировки в библиотеку
library.add(faSort, faSortUp, faSortDown);
</script>

<template>
    <Head title="Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Tickets
                </h2>
                <div
                    class="flex flex-wrap items-center justify-end gap-2 min-h-8 mb-4"
                >
                    <Link v-if="isUser" :href="route('ticket.create')">
                        <button
                            class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <font-awesome-icon
                                icon="fa-solid fa-plus"
                                class="h-5 w-5 mr-1"
                            />
                            New Ticket
                        </button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white shadow-sm sm:rounded-lg p-6 overflow-hidden"
                >
                    <!-- Filter component -->
                    <TicketFilter
                        :filters="filters"
                        :is-admin="isAdmin"
                        @filter-change="handleFilterChange"
                        @reset-filters="resetFilters"
                    />

                    <!-- Информация о записях и выбор количества -->
                    <div class="flex flex-wrap justify-between items-center mb-4">
                        <div class="text-sm text-gray-700">
                            Showing {{ tickets.from }} - {{ tickets.to }} of {{ tickets.total }} records
                        </div>
                        <div class="flex items-center space-x-2">
                            <label for="per-page" class="text-sm text-gray-600">
                                Records per page:
                            </label>
                            <select
                                id="per-page"
                                v-model="perPage"
                                @change="changePerPage"
                                class="text-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm"
                            >
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <!-- Таблица в контейнере с прокруткой -->
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
                                            @click="() => deleteConfirm(ticket.id)"
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

                    <!-- Отступ между таблицей и пагинацией -->
                    <div class="my-6"></div>

                    <!-- Pagination -->
                    <div
                        class="pagination-container px-4 py-3 flex justify-center"
                    >
                        <nav class="flex rounded-md" aria-label="Pagination">
                            <!-- Previous Page -->
                            <Link
                                v-if="tickets.prev_page_url"
                                :href="getModifiedUrl(tickets.prev_page_url)"
                                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50"
                            >
                                <font-awesome-icon
                                    class="h-5 w-5"
                                    icon="fa-solid fa-chevron-left"
                                />
                                <span class="sr-only">Предыдущая</span>
                            </Link>
                            <span
                                v-else
                                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-not-allowed"
                            >
                                <font-awesome-icon
                                    class="h-5 w-5"
                                    icon="fa-solid fa-chevron-left"
                                />
                                <span class="sr-only">Предыдущая</span>
                            </span>

                            <!-- Page Links -->
                            <template
                                v-for="link in tickets.links.slice(1, -1)"
                                :key="link.label"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="getModifiedUrl(link.url)"
                                    :class="[
                                        link.active
                                            ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                        'relative inline-flex items-center px-4 py-2 text-sm font-medium border',
                                    ]"
                                >
                                    {{ link.label }}
                                </Link>
                                <span
                                    v-else
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                                >
                                    ...
                                </span>
                            </template>

                            <!-- Next Page -->
                            <Link
                                v-if="tickets.next_page_url"
                                :href="getModifiedUrl(tickets.next_page_url)"
                                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50"
                            >
                                <span class="sr-only">Следующая</span>
                                <font-awesome-icon
                                    class="h-5 w-5"
                                    icon="fa-solid fa-chevron-right"
                                />
                            </Link>
                            <span
                                v-else
                                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-not-allowed"
                            >
                                <span class="sr-only">Следующая</span>
                                <font-awesome-icon
                                    class="h-5 w-5"
                                    icon="fa-solid fa-chevron-right"
                                />
                            </span>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Адаптивные стили */
@media (max-width: 768px) {
    .grid {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
}

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

.pagination-container {
    width: 100%;
    padding: 0.75rem 1rem;
    box-sizing: border-box;
    display: flex;
    justify-content: center;
    border-top: 1px solid #e5e7eb;
}
</style>
