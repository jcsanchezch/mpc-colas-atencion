<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { onMounted, ref } from 'vue';

const props = defineProps({
    pendingTickets: Array,
    activeTicket: Object,
    userDesk: String,
});

const isProcessing = ref(false);
const cooldownSeconds = ref(0);
let cooldownTimer = null;

const startCooldown = () => {
    cooldownSeconds.value = 15;
    if (cooldownTimer) clearInterval(cooldownTimer);
    cooldownTimer = setInterval(() => {
        cooldownSeconds.value--;
        if (cooldownSeconds.value <= 0) {
            clearInterval(cooldownTimer);
        }
    }, 1000);
};

onMounted(() => {
    if (window.Echo) {
        window.Echo.channel('public.tickets')
            .listen('TicketCreated', () => reloadData())
            .listen('TicketCalled', () => reloadData())
            .listen('TicketStatusUpdated', () => reloadData());
    }
});

const reloadData = () => {
    router.reload({ only: ['pendingTickets', 'activeTicket'], preserveScroll: true });
};

const callNext = () => {
    isProcessing.value = true;
    router.post(route('clerk.callNext'), {}, {
        preserveScroll: true,
        onSuccess: () => startCooldown(),
        onFinish: () => isProcessing.value = false,
    });
};

const recallTurno = (turnoId) => {
    isProcessing.value = true;
    router.post(route('clerk.reCall', turnoId), {}, {
        preserveScroll: true,
        onSuccess: () => startCooldown(),
        onFinish: () => isProcessing.value = false,
    });
};

const abandonTurno = (turnoId) => {
    if (!confirm('¿Seguro de marcar turno como abandonado?')) return;
    isProcessing.value = true;
    router.post(route('clerk.abandon', turnoId), {}, {
        preserveScroll: true,
        onFinish: () => isProcessing.value = false,
    });
};

const serveTurno = (turnoId) => {
    isProcessing.value = true;
    router.post(route('clerk.serve', turnoId), {}, {
        preserveScroll: true,
        onFinish: () => isProcessing.value = false,
    });
};

const completeTurno = (turnoId) => {
    isProcessing.value = true;
    router.post(route('clerk.complete', turnoId), {}, {
        preserveScroll: true,
        onFinish: () => isProcessing.value = false,
    });
};
</script>

<template>
    <Head title="Panel de Atención" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Módulo de Atención - {{ userDesk || 'Sin Ventanilla' }}
                </h2>
                <div class="flex items-center gap-2 text-sm text-green-500 font-medium">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    Conectado (En vivo)
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-12 gap-6">

                <!-- Left Column: Current Attention -->
                <div class="md:col-span-7 space-y-6">
                    <!-- Notifications -->
                    <div v-if="$page.props.errors?.message" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                        <span class="block sm:inline">{{ $page.props.errors.message }}</span>
                    </div>
                    <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                        <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                    </div>

                    <!-- Active Ticket Panel -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-bold text-gray-700 dark:text-gray-300">Turno en Atención</h3>
                        </div>

                        <div class="p-8 text-center" v-if="activeTicket">
                            <div class="text-gray-500 dark:text-gray-400 mb-2">{{ activeTicket.tramite.nombre }}</div>
                            <div class="text-6xl font-black text-blue-600 dark:text-blue-400 mb-8">{{ activeTicket.dni }}</div>

                            <!-- State: Called -->
                            <div v-if="activeTicket.estado === 'LLAMANDO'" class="flex gap-4 justify-center">
                                <button
                                    @click="recallTurno(activeTicket.id)"
                                    :disabled="isProcessing || cooldownSeconds > 0"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors disabled:opacity-50">
                                    {{ cooldownSeconds > 0 ? `Esperar ${cooldownSeconds}s...` : 'Volver a Llamar' }}
                                </button>
                                <button
                                    v-if="activeTicket.cantidad_llamados >= 3 && cooldownSeconds === 0"
                                    @click="abandonTurno(activeTicket.id)"
                                    :disabled="isProcessing"
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors disabled:opacity-50">
                                    Abandonar Turno
                                </button>
                                <button
                                    @click="serveTurno(activeTicket.id)"
                                    :disabled="isProcessing"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors disabled:opacity-50">
                                    Iniciar Atención
                                </button>
                            </div>

                            <!-- State: Serving -->
                            <div v-else-if="activeTicket.estado === 'ATENDIENDO'" class="flex gap-4 justify-center">
                                <button
                                    @click="completeTurno(activeTicket.id)"
                                    :disabled="isProcessing"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors disabled:opacity-50 w-full max-w-xs">
                                    Finalizar Atención
                                </button>
                            </div>
                        </div>

                        <div v-else class="p-12 text-center text-gray-400 dark:text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xl">No hay turno en atención en este momento.</p>
                        </div>
                    </div>

                    <!-- Call Next Button (Only visible if no active ticket) -->
                    <div v-if="!activeTicket">
                        <button
                            @click="callNext"
                            :disabled="isProcessing || pendingTickets.length === 0"
                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-5 px-6 rounded-2xl shadow-lg transition-all transform hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none text-xl"
                        >
                            Llamar Siguiente Turno
                        </button>
                    </div>
                </div>

                <!-- Right Column: Queue -->
                <div class="md:col-span-5">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700 h-full flex flex-col">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                            <h3 class="font-bold text-gray-700 dark:text-gray-300">Turnos en Espera</h3>
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full dark:bg-blue-900 dark:text-blue-300">
                                {{ pendingTickets.length }}
                            </span>
                        </div>

                        <div class="p-4 flex-1 overflow-y-auto max-h-[600px]">
                            <div v-if="pendingTickets.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-8">
                                La cola está vacía.
                            </div>

                            <ul v-else class="space-y-3">
                                <li v-for="turno in pendingTickets" :key="turno.id" class="flex justify-between items-center p-3 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    <div>
                                        <div class="font-bold text-gray-900 dark:text-gray-100 text-lg">
                                            {{ turno.dni }}
                                            <span class="font-normal text-sm text-gray-500 dark:text-gray-400 ml-1">{{ turno.numero }}</span>
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ turno.tramite.nombre }}</div>
                                    </div>
                                    <div class="flex items-center">
                                        <span v-if="turno.tiene_discapacidad" class="text-blue-600 dark:text-blue-400 mr-3" title="Prioridad / Discapacidad">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                            </svg>
                                        </span>
                                        <span class="text-xs text-gray-400">{{ new Date(turno.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
