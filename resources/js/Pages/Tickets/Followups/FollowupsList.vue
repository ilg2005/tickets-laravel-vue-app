<script setup>
import { ref } from 'vue';
import FollowupItem from './FollowupItem.vue';
import { useToast } from "primevue/usetoast";
import { useConfirm } from "primevue/useconfirm";
import ScrollPanel from 'primevue/scrollpanel';
import { router } from '@inertiajs/vue3';
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
    }
});

const emit = defineEmits(['followupDeleted', 'followupUpdated']);

const toast = useToast();
const confirm = useConfirm();
const page = usePage();

const deleteConfirm = (followupId) => {
    confirm.require({
        message: 'Are you sure you want to proceed?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
            emit('followupDeleted', followupId);
            setTimeout(() => {
                window.location.reload();
            }, 500);
        },
        reject: () => {},
        acceptLabel: 'Yes',
        rejectLabel: 'No',
        acceptIcon: 'pi pi-check',
        rejectIcon: 'pi pi-times',
        acceptClass: 'p-button-danger',
        rejectClass: 'p-button-secondary p-button-outlined',
    });
};
</script>

<template>
    <div>
        <h2 class="text-lg font-semibold mb-4">Followups</h2>
        <ScrollPanel style="width: 100%;">
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
        </ScrollPanel>
    </div>
</template>
