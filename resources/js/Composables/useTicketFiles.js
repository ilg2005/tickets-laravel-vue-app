import { ref } from 'vue';


export function useTicketFiles(initialFiles = []) {
    
    const files = ref(initialFiles || []);

    const updateFiles = (newFiles) => {
        files.value = newFiles;
    };
    
    const formatFileSize = (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    
    const handleFileChange = (event, form) => {
        if (!form) return;
        
        form.files = event.target.files;
        // Очистка всех ошибок, связанных с файлами
        form.clearErrors('files');
        Object.keys(form.errors).forEach(key => {
            if (key.startsWith('files.')) {
                form.clearErrors(key);
            }
        });
    };

    
    const getDownloadUrl = (file) => {
        return file.is_followup 
            ? route('ticket.followups.files.download', { file_id: file.id })
            : route('ticket.files.download', { file_id: file.id });
    };

    
    const formatDate = (timestamp) => {
        if (!timestamp) return '';
        return new Date(timestamp).toLocaleString();
    };

    return {
        files,
        updateFiles,
        formatFileSize,
        handleFileChange,
        getDownloadUrl,
        formatDate
    };
} 