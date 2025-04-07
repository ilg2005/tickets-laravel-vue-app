<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

const props = defineProps({
    links: {
        type: Array,
        required: true
    },
    prevPageUrl: {
        type: String,
        default: null
    },
    nextPageUrl: {
        type: String,
        default: null
    },
    perPage: {
        type: [Number, String],
        required: true
    }
});

// Функция для модификации URL пагинации, добавляя параметр per_page
const getModifiedUrl = (url) => {
    if (!url) return null;
    const urlObj = new URL(url);
    urlObj.searchParams.set("per_page", Number(props.perPage));
    return urlObj.href;
};
</script>

<template>
    <div class="pagination-container px-4 py-3 flex justify-center">
        <nav class="flex rounded-md" aria-label="Pagination">
            <!-- Previous Page -->
            <Link
                v-if="prevPageUrl"
                :href="getModifiedUrl(prevPageUrl)"
                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50"
            >
                <FontAwesomeIcon
                    class="h-5 w-5"
                    icon="fa-solid fa-chevron-left"
                />
                <span class="sr-only">Предыдущая</span>
            </Link>
            <span
                v-else
                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-not-allowed"
            >
                <FontAwesomeIcon
                    class="h-5 w-5"
                    icon="fa-solid fa-chevron-left"
                />
                <span class="sr-only">Предыдущая</span>
            </span>

            <!-- Page Links -->
            <template v-for="link in links.slice(1, -1)" :key="link.label">
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
                v-if="nextPageUrl"
                :href="getModifiedUrl(nextPageUrl)"
                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 hover:bg-gray-50"
            >
                <span class="sr-only">Следующая</span>
                <FontAwesomeIcon
                    class="h-5 w-5"
                    icon="fa-solid fa-chevron-right"
                />
            </Link>
            <span
                v-else
                class="relative inline-flex items-center px-3 py-2 text-sm font-medium text-gray-300 bg-white border border-gray-300 cursor-not-allowed"
            >
                <span class="sr-only">Следующая</span>
                <FontAwesomeIcon
                    class="h-5 w-5"
                    icon="fa-solid fa-chevron-right"
                />
            </span>
        </nav>
    </div>
</template>

<style scoped>
.pagination-container {
    width: 100%;
    padding: 0.75rem 1rem;
    box-sizing: border-box;
    display: flex;
    justify-content: center;
    border-top: 1px solid #e5e7eb;
}
</style> 