<script setup>
import { useForm, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/Button.vue';

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

// Функция для форматирования размера файлов
const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    else if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    else return (bytes / 1048576).toFixed(2) + ' MB';
};
</script>

<template>
    <form @submit.prevent="submit" class="w-full">
        <div class="gap-4 p-4 grid grid-cols-3">
            <div class="mb-4 col-span-2">
                <label for="followup-content" class="block text-sm font-medium">Follow-up Content</label>
                <textarea v-model="form.content" id="followup-content"
                    class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                    rows="6"></textarea>
                <InputError :message="form.errors.content" class="mt-2" />
            </div>

            <div>
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
                    <label for="files" class="block text-sm font-medium">Attach Files</label>
                    <p class="text-xs text-gray-500 mb-1">Max. 10MB per file. Allowed: jpg, png, pdf, doc(x), zip, rar, txt</p>
                    <input
                        id="files"
                        type="file"
                        multiple
                        @change="handleFileChange"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 border border-gray-300 rounded-md shadow-sm cursor-pointer focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                    />
                    <div v-if="form.files && form.files.length > 0" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        <p>Selected files:</p>
                        <ul>
                            <li v-for="file in Array.from(form.files)" :key="file.name">
                                {{ file.name }} ({{ (file.size / 1024).toFixed(2) }} KB)
                            </li>
                        </ul>
                    </div>
                    <InputError class="mt-2" :message="form.errors.files" />
                    <div v-for="(error, index) in form.errors" :key="index">
                        <InputError v-if="index.startsWith('files.')" class="mt-1" :message="error" />
                    </div>
                </div>
                
                <div class="flex justify-end gap-2">
                    <Button @click="form.reset(); form.clearErrors()" class="text-secondary">Cancel</Button>
                    <Button type="submit" class="text-primary">Add Follow-up</Button>
                </div>
            </div>
        </div>
    </form>
</template>
