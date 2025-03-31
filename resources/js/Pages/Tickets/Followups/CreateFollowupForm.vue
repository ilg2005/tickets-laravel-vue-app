<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import Button from 'primevue/button';

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

const emit = defineEmits(['created']);

const form = useForm({
    content: '',
    type: 'comment',
    ticket_id: props.ticketId
});

const submit = () => {
    form.post(route('followups.store'), {
        onSuccess: (response) => {
            emit('created', response.props.ticket.followups[response.props.ticket.followups.length - 1]);
            form.reset();
        }
    });
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
                <div class="flex justify-end gap-2">
                    <Button label="Cancel" severity="secondary" type="button"
                        @click="form.reset(); form.clearErrors()" />
                    <Button severity="primary" type="submit">Add Follow-up</Button>
                </div>
            </div>
        </div>
    </form>
</template>
