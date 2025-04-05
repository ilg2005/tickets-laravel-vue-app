<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CreateTicketForm from './Partial/CreateTicketForm.vue';
import { Head, usePage } from '@inertiajs/vue3';

const { props } = usePage();

// Функция для форматирования даты
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

// Функция для форматирования размера файла
const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    else if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    else return (bytes / 1048576).toFixed(2) + ' MB';
};
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
                    <CreateTicketForm :ticket="props.ticket" :all-files="props.allFiles" mode="show" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
