<script setup>
// Этот файл является заглушкой для обратной совместимости
// Используйте TextInput вместо InputText
import TextInput from '@/Components/TextInput.vue';

const model = defineModel({
    type: String,
    required: true,
});
</script>

<template>
    <TextInput v-model="model" v-bind="$attrs" />
</template> 