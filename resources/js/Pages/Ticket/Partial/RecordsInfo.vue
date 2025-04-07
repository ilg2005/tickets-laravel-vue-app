<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    from: {
        type: Number,
        required: true
    },
    to: {
        type: Number,
        required: true
    },
    total: {
        type: Number,
        required: true
    },
    perPage: {
        type: [Number, String],
        required: true
    }
});

const emit = defineEmits(['perPageChange']);

// Преобразуем входящее значение в число
const perPageLocal = ref(Number(props.perPage));

watch(() => props.perPage, (newValue) => {
    perPageLocal.value = Number(newValue);
});

const changePerPage = () => {
    emit('perPageChange', Number(perPageLocal.value));
};
</script>

<template>
    <div class="flex flex-wrap justify-between items-center mb-4">
        <div class="text-sm text-gray-700">
            Showing {{ from }} - {{ to }} of {{ total }} records
        </div>
        <div class="flex items-center space-x-2">
            <label for="per-page" class="text-sm text-gray-600">
                Records per page:
            </label>
            <select
                id="per-page"
                v-model="perPageLocal"
                @change="changePerPage"
                class="text-sm border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >
                <option :value="10">10</option>
                <option :value="15">15</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
            </select>
        </div>
    </div>
</template> 