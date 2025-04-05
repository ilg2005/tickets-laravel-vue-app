<script setup>
import { useForm, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import FileUploader from '@/Components/FileUploader.vue';

const props = defineProps({
    ticketId: {
        type: Number,
        required: true
    },
    canCreateSolution: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['created', 'files-updated']);

const form = useForm({
    content: '',
    type: 'comment',
    ticket_id: props.ticketId,
    files: []
});

const submit = () => {
    form.post(route('followups.store'), {
        onSuccess: () => {
            // Простая перезагрузка страницы после успешного добавления
            window.location.reload();
        },
        forceFormData: true
    });
};

const handleFileChange = (e) => {
    form.files = e.target.files;
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
};

// Функция для форматирования размера файлов
const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    else if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    else return (bytes / 1048576).toFixed(2) + ' MB';
};
</script>

<template>
    <form @submit.prevent="submit" class="w-full mt-6">
        <div class="gap-4 p-4 grid-layout">
            <div class="content-column mb-4">
                <label for="followup-content" class="block text-sm font-medium">Follow-up Content</label>
                <textarea v-model="form.content" id="followup-content"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                    rows="6"></textarea>
                <InputError :message="form.errors.content" class="mt-2" />
            </div>

            <div class="config-column min-w-[250px]">
                <div class="mb-4">
                    <label for="followup-type" class="block text-sm font-medium">Follow-up Type</label>
                    <select :disabled="!canCreateSolution" v-model="form.type"
                        id="followup-type"
                        class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                        <option value="comment">Comment</option>
                        <option value="solution">Solution</option>
                    </select>
                    <InputError :message="form.errors.type" class="mt-2" />
                </div>
                
                <div class="mb-4">
                    <FileUploader
                        v-model="form.files"
                        :errors="form.errors"
                        fieldName="files"
                        @file-change="handleFileChange"
                    />
                </div>
                
                <div class="flex justify-center gap-2 button-container">
                    <button 
                        type="button"
                        @click="resetForm"
                        class="inline-flex items-center justify-center h-8 px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="inline-flex items-center justify-center h-8 px-3 py-1.5 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 whitespace-nowrap"
                    >
                        Add Follow-up
                    </button>
                </div>
            </div>
        </div>
    </form>
</template>

<style scoped>
.grid-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
}

.content-column {
    grid-column: 1;
}

.config-column {
    grid-column: 2;
}

/* Адаптивная верстка для планшетов */
@media (max-width: 1024px) {
    .grid-layout {
        grid-template-columns: 1.5fr 1fr;
        gap: 16px;
    }
}

/* Адаптивная верстка для мобильных устройств */
@media (max-width: 768px) {
    .grid-layout {
        grid-template-columns: 1fr;
    }
    
    .content-column {
        grid-column: 1;
        grid-row: 1;
    }
    
    .config-column {
        grid-column: 1;
        grid-row: 2;
        margin-top: 16px;
    }
    
    .button-container {
        justify-content: space-between;
    }
    
    .button-container button {
        flex: 1;
        min-width: 0;
        text-align: center;
    }
}

/* Для совсем маленьких экранов */
@media (max-width: 480px) {
    .button-container {
        flex-direction: column;
        gap: 8px;
    }
    
    .button-container button {
        width: 100%;
    }
}
</style>
