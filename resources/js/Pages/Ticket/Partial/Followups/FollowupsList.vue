<script setup>
import FollowupItem from './FollowupItem.vue';
import { useNotification } from '@/Composables/useNotification';

const props = defineProps({
    followups: {
        type: Array,
        required: true
    },
    isEdit: {
        type: Boolean,
        default: false
    },
    currentUser: {
        type: Object,
        required: true
    },
    ticketId: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['followupDeleted', 'followupUpdated']);
const { confirm } = useNotification();

const deleteConfirm = (followupId) => {
    if (confirm('Are you sure you want to delete this followup?')) {
        emit('followupDeleted', followupId);
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
};
</script>

<template>
    <div>        
        <div class="overflow-auto" >
            <template v-if="followups && followups.length > 0">
                <FollowupItem
                    v-for="followup in followups"
                    :key="followup.id"
                    :followup="followup"
                    :is-edit="isEdit"
                    :current-user="currentUser"
                    @delete="deleteConfirm"
                    @update="(data) => emit('followupUpdated', data)"
                />
            </template>
            <template v-else>
                <div class="bg-white shadow rounded-lg p-4 text-center">
                    <p class="text-gray-500">No followups</p>
                </div>
            </template>
        </div>
    </div>
</template>
