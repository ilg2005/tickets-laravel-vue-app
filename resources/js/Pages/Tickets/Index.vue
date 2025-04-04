<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { useForm, Head, Link, usePage, router } from "@inertiajs/vue3";
import CreateTicketForm from "./CreateTicketForm.vue";
import TicketFilter from "@/Pages/Tickets/TicketFilter.vue";
import { ref, computed } from "vue";
import Button from "@/Components/Button.vue";
import { usePermission } from "@/Composables/permissions.js";

const page = usePage();
const { flash } = page.props;

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
        route("tickets.index"),
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
        form.delete(route("tickets.destroy", { id: id }), {
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
            return "text-green-400";
        case "medium":
            return "text-yellow-400";
        case "high":
            return "text-red-400";
        default:
            return "text-gray-400";
    }
};

// Функция для модификации URL пагинации, добавляя параметр per_page
const getModifiedUrl = (url) => {
    if (!url) return null;
    const urlObj = new URL(url);
    urlObj.searchParams.set("per_page", perPage.value);
    return urlObj.href;
};
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
                    <Link v-if="isUser" :href="route('tickets.create')">
                        <button
                            class="inline-flex items-center px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-5 w-5 mr-1"
                                viewBox="0 0 20 20"
                                fill="currentColor"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            Add Ticket
                        </button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6"
                >
                    <!-- Filter component -->
                    <TicketFilter
                        :filters="filters"
                        :is-admin="isAdmin"
                        @filter-change="handleFilterChange"
                        @reset-filters="resetFilters"
                    />

                    <div class="overflow-x-auto">
                        <div
                            class="grid grid-cols-7 gap-4 text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50"
                        >
                            <div
                                @click="onSort('id')"
                                class="px-4 py-2 cursor-pointer"
                            >
                                #
                            </div>
                            <div
                                v-if="isAdmin"
                                @click="onSort('user_name')"
                                class="px-4 py-2 cursor-pointer"
                            >
                                User
                            </div>
                            <div
                                @click="onSort('title')"
                                class="px-4 py-2 cursor-pointer"
                            >
                                Title
                            </div>
                            <div class="px-4 py-2">Description</div>
                            <div
                                @click="onSort('status')"
                                class="px-2 py-2 cursor-pointer"
                            >
                                Status
                            </div>
                            <div
                                @click="onSort('priority')"
                                class="px-2 py-2 cursor-pointer"
                            >
                                Priority
                            </div>
                            <div class="px-4 py-2">Actions</div>
                        </div>
                        <div
                            v-for="ticket in tickets.data"
                            :key="ticket.id"
                            class="grid grid-cols-7 gap-4 text-sm text-gray-500 divide-y divide-gray-200"
                        >
                            <div class="px-4 py-2">{{ ticket.id }}</div>
                            <div v-if="isAdmin" class="px-4 py-2">
                                {{ ticket.user ? ticket.user.name : "-" }}
                            </div>
                            <div class="px-4 py-2">{{ ticket.title }}</div>
                            <div class="px-4 py-2">
                                {{ ticket.description }}
                            </div>
                            <div class="px-2 py-2">
                                <span
                                    :class="
                                        getPriorityColorClass(ticket.status)
                                    "
                                >
                                    {{ ticket.status }}
                                </span>
                            </div>
                            <div class="px-2 py-2">
                                <span
                                    :class="
                                        getPriorityColorClass(ticket.priority)
                                    "
                                >
                                    {{ ticket.priority }}
                                </span>
                            </div>
                            <div class="px-4 py-2">
                                <div class="flex space-x-2">
                                    <Link
                                        :href="route('tickets.show', ticket.id)"
                                        class="p-2 text-blue-500 hover:text-blue-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
                                        title="View"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                d="M10 12a2 2 0 100-4 2 2 0 000 4z"
                                            />
                                            <path
                                                fill-rule="evenodd"
                                                d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('tickets.edit', ticket.id)"
                                        class="p-2 text-yellow-500 hover:text-yellow-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
                                        title="Edit"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                                            />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="() => deleteConfirm(ticket.id)"
                                        class="p-2 text-red-500 hover:text-red-700 transition-colors duration-150 rounded-md hover:bg-gray-100"
                                        title="Delete"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div
                        class="mt-6 px-4 py-3 border-t border-gray-200 sm:px-6"
                    >
                        <div
                            class="flex flex-col sm:flex-row justify-between items-center"
                        >
                            <div class="text-sm text-gray-700 mb-4 sm:mb-0">
                                Показано {{ tickets.from }} -
                                {{ tickets.to }} из {{ tickets.total }} записей
                            </div>
                            <div class="flex items-center space-x-4">
                                <nav
                                    class="inline-flex shadow-sm -space-x-px rounded-md overflow-hidden"
                                    aria-label="Pagination"
                                >
                                    <!-- Previous Page -->
                                    <Link
                                        v-if="tickets.prev_page_url"
                                        :href="
                                            getModifiedUrl(
                                                tickets.prev_page_url
                                            )
                                        "
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span class="sr-only">Предыдущая</span>
                                    </Link>
                                    <span
                                        v-else
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-not-allowed"
                                    >
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                        <span class="sr-only">Предыдущая</span>
                                    </span>

                                    <!-- Page Links -->
                                    <template
                                        v-for="link in tickets.links.slice(
                                            1,
                                            -1
                                        )"
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
                                        :href="
                                            getModifiedUrl(
                                                tickets.next_page_url
                                            )
                                        "
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50"
                                    >
                                        <span class="sr-only">Следующая</span>
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </Link>
                                    <span
                                        v-else
                                        class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-not-allowed"
                                    >
                                        <span class="sr-only">Следующая</span>
                                        <svg
                                            class="h-5 w-5"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </nav>
                                <div class="flex items-center space-x-2 ml-4">
                                    <label
                                        for="per-page"
                                        class="text-sm text-gray-600"
                                        >Записей:</label
                                    >
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
                        </div>
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
</style>
