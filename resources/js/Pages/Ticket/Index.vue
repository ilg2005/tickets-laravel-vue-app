<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import TicketFilter from "./Partial/TicketFilter.vue";
import RecordsInfo from "./Partial/RecordsInfo.vue";
import TicketsTable from "./Partial/TicketsTable.vue";
import { ref } from "vue";
import { usePermission } from "@/Composables/permissions.js";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from '@fortawesome/fontawesome-svg-core';
import { faSort, faSortUp, faSortDown, faPlus } from '@fortawesome/free-solid-svg-icons';
import Pagination from "@/Components/Pagination.vue";

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
const perPage = ref(Number(15));

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
            per_page: Number(perPage.value),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const handlePerPageChange = (newPerPage) => {
    perPage.value = Number(newPerPage);
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

// Добавляю иконки в библиотеку
library.add(faSort, faSortUp, faSortDown, faPlus);
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
                    <RecordsInfo
                        :from="tickets.from"
                        :to="tickets.to"
                        :total="tickets.total"
                        :per-page="perPage"
                        @per-page-change="handlePerPageChange"
                    />

                    <!-- Таблица тикетов -->
                    <TicketsTable 
                        :tickets="tickets"
                        :sort="sort"
                        :is-admin="isAdmin"
                        @sort="onSort"
                        @delete="deleteConfirm"
                    />

                    <!-- Отступ между таблицей и пагинацией -->
                    <div class="my-6"></div>

                    <!-- Pagination -->
                    <Pagination 
                        :links="tickets.links"
                        :prev-page-url="tickets.prev_page_url"
                        :next-page-url="tickets.next_page_url"
                        :per-page="perPage"
                    />
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
</style>
