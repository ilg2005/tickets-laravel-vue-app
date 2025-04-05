<script setup>
import { ref, computed, onMounted } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    modelValue: {
        type: [FileList, Array, null],
        default: null
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    fieldName: {
        type: String,
        default: 'files'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    inputRef: {
        type: Object,
        default: null
    },
    buttonText: {
        type: String,
        default: 'Select'
    }
});

const emit = defineEmits(['update:modelValue', 'fileChange']);

// Всегда используем локальный ref для доступа к DOM-элементу
const fileInput = ref(null);

// Функция для программного клика на input[type=file]
const triggerFileInput = () => {
    if (!props.disabled && fileInput.value) {
        fileInput.value.click();
    }
};

const handleFileChange = (event) => {
    emit('update:modelValue', event.target.files);
    emit('fileChange', event);
    
    // Если есть внешний ref, синхронизируем его значение
    if (props.inputRef && typeof props.inputRef.value !== 'undefined') {
        if (typeof props.inputRef.value === 'object' && props.inputRef.value !== null) {
            props.inputRef.value.value = event.target.value;
        } else {
            console.warn('External inputRef is not a valid ref object');
        }
    }
};

// Функция для форматирования размера файла
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Проверяем, есть ли ошибки связанные с файлами
const hasFileErrors = computed(() => {
    return Object.keys(props.errors).some(key => 
        key === props.fieldName || key.startsWith(`${props.fieldName}.`)
    );
});

// Получаем все ошибки файлов
const fileErrors = computed(() => {
    return Object.entries(props.errors)
        .filter(([key]) => key === props.fieldName || key.startsWith(`${props.fieldName}.`))
        .map(([_, value]) => value);
});

// Вычисляемое свойство для отображения имен выбранных файлов
const selectedFileNames = computed(() => {
    if (!props.modelValue || props.modelValue.length === 0) {
        return 'No files selected';
    }
    
    const fileList = Array.from(props.modelValue);
    if (fileList.length === 1) {
        return fileList[0].name;
    }
    
    return `${fileList.length} files selected`;
});
</script>

<template>
    <div class="file-uploader">
        <label :for="fieldName" class="block text-sm font-medium text-gray-700 mb-1">Attach Files</label>
        <p class="text-xs text-gray-500 mb-1">Max. 10MB per file. Allowed: jpg, png, pdf, doc(x), zip, rar, txt</p>
        
        <!-- Скрытый input для файлов, всегда используем локальный ref -->
        <input
            :id="fieldName"
            ref="fileInput"
            type="file"
            multiple
            :disabled="disabled"
            @change="handleFileChange"
            class="hidden"
        />
        
        <!-- Кастомный UI для выбора файлов -->
        <div class="file-input-ui flex relative">
            <button 
                type="button" 
                @click="triggerFileInput"
                :disabled="disabled"
                class="inline-flex items-center px-3 py-2 bg-violet-50 border border-violet-200 rounded-l-md font-semibold text-xs text-violet-700 uppercase hover:bg-violet-100 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 transition-colors relative z-10"
                :class="{ 'opacity-60 cursor-not-allowed': disabled }"
            >
                {{ buttonText }}
            </button>
            <div 
                @click="triggerFileInput"
                class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 border-l-0 rounded-r-md text-sm text-gray-500 flex-grow truncate cursor-pointer hover:bg-gray-50"
                :class="{ 'opacity-60 cursor-not-allowed': disabled }"
            >
                {{ selectedFileNames }}
            </div>
        </div>
        
        <div v-if="modelValue && modelValue.length > 0" class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <p>Selected files:</p>
            <ul class="max-h-32 overflow-y-auto">
                <li v-for="file in Array.from(modelValue)" :key="file.name" class="truncate">
                    {{ file.name }} ({{ formatFileSize(file.size) }})
                </li>
            </ul>
        </div>
        
        <InputError v-if="hasFileErrors" class="mt-2" :message="props.errors[fieldName]" />
        <div v-for="(error, index) in fileErrors" :key="index">
            <InputError class="mt-1" :message="error" />
        </div>
    </div>
</template>

<style scoped>
.file-input-ui button:focus {
    box-shadow: 0 0 0 2px white, 0 0 0 4px #c4b5fd;
}

.file-input-ui button:active {
    transform: translateY(1px);
    box-shadow: 0 0 0 1px white, 0 0 0 3px #c4b5fd;
}
</style>