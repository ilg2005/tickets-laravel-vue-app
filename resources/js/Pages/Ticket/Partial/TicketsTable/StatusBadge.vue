<script setup>
const props = defineProps({
    status: {
        type: String,
        required: true
    }
});

// Функция для получения класса цвета Tailwind CSS для статуса
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
    <span :class="getStatusColorClass(status)">
        {{ status.replace('_', ' ') }}
    </span>
</template> 