<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import TicketFilter from "./Partial/TicketFilter.vue";
import RecordsInfo from "./Partial/RecordsInfo.vue";
import TableIndex from "./Partial/TicketsTable/TableIndex.vue";
import { ref } from "vue";
import { usePermission } from "@/Composables/permissions.js";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
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
                    <!-- Фильтры для тикетов -->
                    <TicketFilter
                        :filters="filters"
                        :is-admin="isAdmin"
                        @filter-change="handleFilterChange"
                        @reset-filters="resetFilters"
                    />

                    <!-- Информация о записях на странице и выбор количества -->
                    <RecordsInfo
                        :from="tickets.from"
                        :to="tickets.to"
                        :total="tickets.total"
                        :per-page="perPage"
                        @per-page-change="handlePerPageChange"
                    />

                    <!-- Таблица тикетов -->
                    <TableIndex 
                        :tickets="tickets"
                        :sort="sort"
                        :is-admin="isAdmin"
                        @sort="onSort"
                        @delete="deleteConfirm"
                    />                   

                    <!-- Пагинация -->
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
