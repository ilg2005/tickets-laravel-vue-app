<script setup>
import { ref } from 'vue';
import FollowupItem from './FollowupItem.vue';
import { router, Link } from '@inertiajs/vue3';
import { usePage } from "@inertiajs/vue3";

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

const page = usePage();

const deleteConfirm = (followupId) => {
    if (confirm('Are you sure you want to proceed?')) {
        emit('followupDeleted', followupId);
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
};
</script>

<template>
    <div>        
        <div class="overflow-auto" style="max-height: 400px;">
            <template v-if="followups.length > 0">
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
