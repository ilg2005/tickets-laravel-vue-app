<script setup>
const props = defineProps({
    priority: {
        type: String,
        required: true
    }
});

// Функция для получения класса цвета для приоритета
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
</script>

<template>
    <div class="flex items-center">
        <span 
            :class="[getPriorityColorClass(priority), 'inline-block w-3 h-3 rounded-full mr-2']"
        ></span>
        <span class="text-gray-700">{{ priority }}</span>
    </div>
</template> 