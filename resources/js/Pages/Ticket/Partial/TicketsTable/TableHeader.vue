<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

const props = defineProps({
    sort: {
        type: Object,
        required: true,
    },
    isAdmin: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["sort"]);

const onSort = (field) => {
    emit("sort", field);
};

// Колонки таблицы
const columns = [
    { id: "id", label: "#" }, // ID тикета
    { id: "user_name", label: "User", showOnlyAdmin: true }, // Имя пользователя (видно только админам)
    { id: "title", label: "Title" }, // Заголовок тикета
    { id: "description", label: "Description", sortable: false }, // Описание (без возможности сортировки)
    { id: "status", label: "Status" }, // Статус тикета
    { id: "priority", label: "Priority" }, // Приоритет тикета
    { id: "actions", label: "Actions", sortable: false, center: true }, // Колонка с действиями (центрирована)
];

const getSortIcon = (field) => {
    // Если текущее поле сортировки не совпадает с проверяемым
    if (props.sort.field !== field) {
        return { icon: "fa-solid fa-sort", class: "text-gray-400" }; // Серая иконка по умолчанию
    }

    // Если это активное поле сортировки, показываем направление (вверх или вниз)
    return {
        icon:
            props.sort.order === "asc"
                ? "fa-solid fa-sort-up"
                : "fa-solid fa-sort-down",
        class: "",
    };
};
</script>

<template>
    <div
        class="grid-filter-container text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100"
    >
        <!-- Перебираем все колонки из массива -->
        <template v-for="column in columns" :key="column.id">
            <div
                v-if="!column.showOnlyAdmin || isAdmin"
                :class="[
                    `${column.id}-cell`,
                    column.sortable !== false ? 'cursor-pointer' : '',
                    column.center ? 'text-center' : '',
                ]"
                @click="column.sortable !== false ? onSort(column.id) : null"
            >
                <div
                    class="flex items-center"
                    :class="{ 'justify-center': column.center }"
                >
                    {{ column.label }}

                    <span v-if="column.sortable !== false" class="ml-1">
                        <!-- Иконка сортировки -->
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
/* Общий контейнер таблицы */
.grid-filter-container {
    display: grid;
    grid-template-columns: 5% 10% 12% 33% 12% 12% 10%; /* Ширина колонок в процентах */
    gap: 0;
    align-items: center;
    width: 100%;
    box-sizing: border-box;
    min-width: 800px; /* Минимальная ширина таблицы для предотвращения сжатия */
}

/* Модификация сетки когда не админ - скрываем колонку пользователя */
:root:not(.admin-active) .grid-filter-container {
    grid-template-columns: 5% 0% 12% 43% 12% 12% 10%; /* Увеличена ширина description за счет скрытия user_name */
}

/* Общие стили для всех ячеек заголовка */
.grid-filter-container > div {
    padding: 0.75rem 0.5rem;
}

/* Позиции отдельных ячеек в сетке */
.id-cell {
    grid-column: 1;
}
.user_name-cell {
    grid-column: 2;
}
.title-cell {
    grid-column: 3;
}
.description-cell {
    grid-column: 4;
}
.status-cell {
    grid-column: 5;
    padding-left: 0.125rem;
    padding-right: 0.125rem;
}
.priority-cell {
    grid-column: 6;
    padding-left: 0.125rem;
    padding-right: 0.125rem;
}
.actions-cell {
    grid-column: 7;
}

/* Адаптивность для мобильных устройств */
@media screen and (max-width: 768px) {
    .grid-filter-container {
        overflow-x: auto; /* Добавляем горизонтальную прокрутку */
        white-space: nowrap; /* Предотвращаем перенос текста */
    }
}
</style>
