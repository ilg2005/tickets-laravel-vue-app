<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import './TableStyles.css';

const props = defineProps({
    sort: {
        type: Object,
        required: true
    },
    isAdmin: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['sort']);

const onSort = (field) => {
    emit('sort', field);
};
</script>

<template>
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
</template> 