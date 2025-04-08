<script setup>
import { useTicketFiles } from '@/Composables/useTicketFiles';

const props = defineProps({
    files: {
        type: Array,
        default: () => []
    },
    isReadOnly: {
        type: Boolean,
        default: false
    }
});

const { formatFileSize, getDownloadUrl, formatDate } = useTicketFiles(props.files);
</script>

<template>
    <div>
        <h3 class="block text-sm font-medium text-gray-700 mb-2">Attached Files</h3>
        <ul v-if="files.length > 0" class="list-none p-0 m-0 border border-gray-300 rounded-md divide-y divide-gray-300">
            <li v-for="file in files" :key="`${file.is_followup ? 'followup' : 'ticket'}-${file.id}`" 
                class="p-3 flex justify-between items-center text-sm">
                <div class="flex items-center overflow-hidden mr-2">                    
                    <a :href="getDownloadUrl(file)"
                        class="text-indigo-600 hover:text-indigo-800 hover:underline truncate"
                        :title="file.original_filename">
                        {{ file.original_filename }}
                        <span v-if="file.is_followup" class="text-gray-500 text-xs">
                            (followup)
                        </span>
                    </a>
                </div>
                <div class="flex-shrink-0 text-gray-500 ml-auto pl-2">
                    ({{ formatFileSize(file.size) }})
                </div>
            </li>
        </ul>        
    </div>
</template> 