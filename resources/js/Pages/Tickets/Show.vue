<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CreateTicketForm from './CreateTicketForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

const { props } = usePage();
</script>

<template>
    <Head title="Show Ticket" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Show Ticket #{{ props.ticket.id }}</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <CreateTicketForm :ticket="props.ticket" mode="show" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
