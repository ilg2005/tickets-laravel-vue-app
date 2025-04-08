<script setup>
import { router, usePage, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { usePermissionCheck } from '@/Composables/usePermissionCheck.js';
import { useTicketFiles } from '@/Composables/useTicketFiles';
import { useNotification } from '@/Composables/useNotification';
import FollowupsList from '../Partial/Followups/FollowupsList.vue';
import CreateFollowupForm from '../Partial/Followups/CreateFollowupForm.vue';
import TicketForm from '../Partial/TicketForm.vue';

const { props } = usePage();
const user = props.auth.user;
const { success, confirm, error } = useNotification();

const { hasPermission, PERMISSIONS } = usePermissionCheck();

const canCreateSolutionFollowups = hasPermission(PERMISSIONS.CREATE_SOLUTION_FOLLOWUPS);

const ticketProps = defineProps({
    ticket: {
        type: Object,
        default: () => ({
            title: '',
            description: '',
            status: 'open',
            priority: 'medium',
            created_at: '',
            updated_at: '',
            followups: [],
            files: [],
        })
    },
    allFiles: {
        type: Array,
        default: () => []
    },
    mode: {
        type: String,
        default: 'create'
    }
});

// Используем composable для работы с файлами
const { files, updateFiles } = useTicketFiles(ticketProps.allFiles || []);

// Отслеживаем изменения файлов из пропсов
watch(() => ticketProps.allFiles, (newFiles) => {
    updateFiles(newFiles);
}, { deep: true });

// Форма для удаления follow-up
const deleteFollowupForm = useForm({});

// Функция для навигации назад
const goBack = () => {
    router.visit(route('ticket.index'));
};

// Обработчик для удаления follow-up
const handleFollowupDelete = (followupId) => {
    deleteFollowupForm.delete(route("ticket.followups.destroy", { id: followupId }), {
        preserveScroll: true,
        onSuccess: () => {
            success('Follow-up deleted successfully');
            // Удаляем followup из локального состояния для мгновенного обновления UI
            if (ticketProps.ticket && ticketProps.ticket.followups) {
                const index = ticketProps.ticket.followups.findIndex(f => f.id === followupId);
                if (index !== -1) {
                    ticketProps.ticket.followups.splice(index, 1);
                }
            }
        },
        onError: (errors) => {
            error('Failed to delete follow-up', errors);
        }
    });
};

// Обработчик для обновления follow-up
const handleFollowupUpdate = (form) => {
    form.put(route('ticket.followups.update', form.followup_id), {
        preserveScroll: true,
        onSuccess: (response) => {
            success('Follow-up updated successfully');
            
            // Перезагружаем страницу для применения обновлений
            window.location.reload();
        },
        onError: (errors) => {
            error('Failed to update follow-up', errors);
        }
    });
};

// Обработчик для создания follow-up
const handleFollowupCreated = (newFollowup) => {
    if (newFollowup && ticketProps.ticket && ticketProps.ticket.followups) {
        ticketProps.ticket.followups.push(newFollowup);
        success('Follow-up added successfully');
    }
};

// Обработчик для обновления списка файлов
const handleFilesUpdated = (updatedFiles) => {
    if (updatedFiles) {
        updateFiles(updatedFiles);
    }
};


</script>

<template>
    <div class="max-w-2xl mx-auto p-4 sm:px-6 md:max-w-full md:space-x-2 lg:px-8">
        <div class="flex mb-10">
            <button 
                @click="goBack" 
                class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </button>
        </div>
        <div class="grid md:grid-cols-3 h-full gap-4 sm:gap-6 md:gap-8">
            <!-- Форма тикета -->
            <div class="w-full min-h-full md:col-span-1">
                <TicketForm 
                    :ticket="ticketProps.ticket"
                    :mode="ticketProps.mode"
                    :files="files"              
                />
            </div>

            <!-- Список follow-ups -->
            <div class="md:col-span-2">
                <div v-if="ticketProps.mode !== 'create'">
                    <FollowupsList
                        :followups="ticketProps.ticket.followups"
                        :is-edit="ticketProps.mode === 'edit'"
                        :current-user="user"
                        :ticket-id="ticketProps.ticket.id"
                        @followup-deleted="handleFollowupDelete"
                        @followup-updated="handleFollowupUpdate"
                    />
                    
                    <!-- Форма для создания follow-up (только для режима редактирования) -->
                    <CreateFollowupForm
                        v-if="ticketProps.mode === 'edit'"
                        :ticket-id="ticketProps.ticket.id"
                        :can-create-solution="canCreateSolutionFollowups"
                        @created="handleFollowupCreated"
                        @files-updated="handleFilesUpdated"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media (max-width: 768px) {
    form {
        margin-bottom: 2rem;
    }
}

@media (max-width: 640px) {
    .button-container {
        flex-direction: column;
        width: 100%;
    }
    
    .button-container button {
        margin-bottom: 0.5rem;
    }
}
</style>
