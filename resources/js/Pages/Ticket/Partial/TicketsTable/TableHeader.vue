<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

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

const columns = [
    { id: 'id', label: '#' },
    { id: 'user_name', label: 'User', showOnlyAdmin: true },
    { id: 'title', label: 'Title' },
    { id: 'description', label: 'Description', sortable: false },
    { id: 'status', label: 'Status' },
    { id: 'priority', label: 'Priority' },
    { id: 'actions', label: 'Actions', sortable: false, center: true }
];

const getSortIcon = (field) => {
    if (props.sort.field !== field) {
        return { icon: 'fa-solid fa-sort', class: 'text-gray-400' };
    }
    
    return {
        icon: props.sort.order === 'asc' ? 'fa-solid fa-sort-up' : 'fa-solid fa-sort-down',
        class: ''
    };
};
</script>

<template>
    <div class="grid-filter-container text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">
        <template v-for="column in columns" :key="column.id">
            <div
                v-if="!column.showOnlyAdmin || isAdmin"
                :class="[
                    `${column.id}-cell`,
                    column.sortable !== false ? 'cursor-pointer' : '',
                    column.center ? 'text-center' : ''
                ]"
                @click="column.sortable !== false ? onSort(column.id) : null"
            >
                <div class="flex items-center" :class="{ 'justify-center': column.center }">
                    {{ column.label }}
                    <span v-if="column.sortable !== false" class="ml-1">
                        <font-awesome-icon
                            :icon="getSortIcon(column.id).icon"
                            class="h-3 w-3"
                            :class="getSortIcon(column.id).class"
                        />
                    </span>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
/* Общий контейнер */
.grid-filter-container {
    display: grid;
    grid-template-columns: 5% 10% 12% 33% 12% 12% 10%;
    gap: 0;
    align-items: center;
    width: 100%;
    box-sizing: border-box;
    min-width: 800px;
}

/* Модификация сетки когда не админ */
:root:not(.admin-active) .grid-filter-container {
    grid-template-columns: 5% 0% 12% 43% 12% 12% 10%;
}

/* Ячейки таблицы - общие стили */
.grid-filter-container > div {
    padding: 0.75rem 0.5rem;
}

/* Индивидуальные ячейки */
.id-cell { grid-column: 1; }
.user_name-cell { grid-column: 2; }
.title-cell { grid-column: 3; }
.description-cell { grid-column: 4; }
.status-cell { grid-column: 5; padding-left: 0.125rem; padding-right: 0.125rem; }
.priority-cell { grid-column: 6; padding-left: 0.125rem; padding-right: 0.125rem; }
.actions-cell { grid-column: 7; }

/* Медиа-запрос для мобильных устройств */
@media screen and (max-width: 768px) {
    .grid-filter-container {
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style> 