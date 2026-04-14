<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    pendingTickets: Array,
    activeTicket: Object,
    ventanilla: Object,
});

// ── Acciones ────────────────────────────────────────────────────────────────

const callNext = () => useForm({}).post(route('tickets.call'));

const reCall = () => useForm({}).post(route('tickets.recall', props.activeTicket.id));

const serve = () => useForm({}).post(route('tickets.serve', props.activeTicket.id));

const complete = () => {
    if (confirm('¿Confirmar atención completada?')) {
        useForm({}).post(route('tickets.complete', props.activeTicket.id));
    }
};

const abandon = () => {
    if (confirm('¿Marcar este ticket como abandonado? El ciudadano no respondió al llamado.')) {
        useForm({}).post(route('tickets.abandon', props.activeTicket.id));
    }
};

// ── Helpers ─────────────────────────────────────────────────────────────────

const estadoColor = (estado) => {
    const map = {
        ESPERANDO:  'bg-yellow-900 text-yellow-300',
        LLAMANDO:   'bg-blue-900 text-blue-300',
        ATENDIENDO: 'bg-emerald-900 text-emerald-300',
        ATENDIDO:   'bg-gray-700 text-gray-400',
        ABANDONADO: 'bg-red-900 text-red-300',
    };
    return map[estado] ?? 'bg-gray-700 text-gray-400';
};

const tiempoEspera = (ticket) => {
    if (!ticket.hora_esperando) return '—';
    const diff = Math.floor((Date.now() - new Date(ticket.hora_esperando)) / 60000);
    return diff < 1 ? '< 1 min' : `${diff} min`;
};

const totalEnEspera = computed(() => props.pendingTickets.length);
const prioritariosEnEspera = computed(() => props.pendingTickets.filter(t => t.prioridad).length);
</script>

<template>
    <Head title="Atención de Tickets" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Atención de Tickets
                </h2>
                <div v-if="ventanilla" class="text-sm text-gray-500">
                    Ventanilla:
                    <span class="font-semibold text-gray-700">{{ ventanilla.codigo }} — {{ ventanilla.nombre }}</span>
                </div>
                <div v-else class="text-sm text-amber-600 font-medium">
                    Sin ventanilla asignada hoy
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- ── Ticket activo ── -->
                <div class="p-6 bg-gray-900 shadow sm:rounded-lg">
                    <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider mb-4">
                        Ticket en curso
                    </h3>

                    <!-- Sin ticket activo -->
                    <div v-if="!activeTicket" class="flex flex-col items-center justify-center py-10 space-y-4">
                        <p class="text-gray-500 text-lg">No hay ticket en atención.</p>
                        <button
                            @click="callNext"
                            :disabled="totalEnEspera === 0"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 rounded-lg text-white font-semibold text-sm hover:bg-blue-500 disabled:opacity-30 disabled:cursor-not-allowed transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Llamar siguiente
                            <span v-if="totalEnEspera > 0" class="bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                                {{ totalEnEspera }}
                            </span>
                        </button>
                    </div>

                    <!-- Con ticket activo -->
                    <div v-else class="space-y-6">
                        <!-- Número grande + datos -->
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                            <div class="flex-shrink-0 w-36 h-36 rounded-2xl flex items-center justify-center"
                                :class="activeTicket.estado === 'ATENDIENDO' ? 'bg-emerald-800' : 'bg-blue-800'">
                                <span class="text-5xl font-black text-white">
                                    {{ String(activeTicket.numero).padStart(3, '0') }}
                                </span>
                            </div>

                            <div class="flex-1 space-y-2 text-center sm:text-left">
                                <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                                    <span :class="estadoColor(activeTicket.estado)"
                                        class="text-xs font-semibold px-3 py-1 rounded-full">
                                        {{ activeTicket.estado }}
                                    </span>
                                    <span v-if="activeTicket.prioridad"
                                        class="bg-orange-900 text-orange-300 text-xs font-semibold px-3 py-1 rounded-full">
                                        PRIORITARIO
                                    </span>
                                </div>
                                <p class="text-gray-100 text-lg font-semibold">
                                    {{ activeTicket.tramite?.nombre ?? '—' }}
                                </p>
                                <p v-if="activeTicket.atencionPrioritaria" class="text-orange-400 text-sm">
                                    {{ activeTicket.atencionPrioritaria.nombre }}
                                </p>
                                <p class="text-gray-500 text-sm">
                                    Llamado: {{ activeTicket.hora_llamando
                                        ? new Date(activeTicket.hora_llamando).toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
                                        : '—' }}
                                </p>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex flex-wrap gap-3 pt-2 border-t border-gray-800">

                            <!-- LLAMANDO -->
                            <template v-if="activeTicket.estado === 'LLAMANDO'">
                                <button @click="reCall"
                                    class="px-4 py-2 bg-blue-700 hover:bg-blue-600 text-white text-sm font-medium rounded-lg transition">
                                    Rellamar
                                </button>
                                <button @click="serve"
                                    class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition">
                                    Iniciar atención
                                </button>
                                <button @click="abandon"
                                    class="px-4 py-2 bg-red-800 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                                    Marcar abandonado
                                </button>
                            </template>

                            <!-- ATENDIENDO -->
                            <template v-if="activeTicket.estado === 'ATENDIENDO'">
                                <button @click="complete"
                                    class="px-4 py-2 bg-emerald-700 hover:bg-emerald-600 text-white text-sm font-medium rounded-lg transition">
                                    Completar atención
                                </button>
                            </template>

                            <!-- Llamar siguiente (si ya completó / no hay activo pero hay cola) -->
                            <button
                                v-if="activeTicket.estado === 'ATENDIDO' || activeTicket.estado === 'ABANDONADO'"
                                @click="callNext"
                                :disabled="totalEnEspera === 0"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-lg disabled:opacity-30 transition">
                                Llamar siguiente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── Cola de espera ── -->
                <div class="p-6 bg-gray-900 shadow sm:rounded-lg">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider">
                            Cola de espera
                        </h3>
                        <div class="flex gap-3 text-xs">
                            <span class="bg-yellow-900 text-yellow-300 px-2 py-1 rounded-full font-medium">
                                {{ totalEnEspera }} en espera
                            </span>
                            <span v-if="prioritariosEnEspera > 0"
                                class="bg-orange-900 text-orange-300 px-2 py-1 rounded-full font-medium">
                                {{ prioritariosEnEspera }} prioritarios
                            </span>
                        </div>
                    </div>

                    <div v-if="pendingTickets.length === 0" class="py-8 text-center text-gray-600">
                        No hay tickets en espera.
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-300">
                            <thead class="text-xs text-gray-400 uppercase bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3">#</th>
                                    <th class="px-4 py-3">Trámite</th>
                                    <th class="px-4 py-3 text-center">Prioridad</th>
                                    <th class="px-4 py-3">Espera</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="ticket in pendingTickets" :key="ticket.id"
                                    class="border-b border-gray-800 hover:bg-gray-800/40"
                                    :class="{ 'bg-orange-950/30': ticket.prioridad }">
                                    <td class="px-4 py-3 font-mono font-bold text-gray-100 text-base">
                                        {{ String(ticket.numero).padStart(3, '0') }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-300">
                                        {{ ticket.tramite?.nombre ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span v-if="ticket.prioridad"
                                            class="bg-orange-900 text-orange-300 text-xs font-medium px-2 py-0.5 rounded-full">
                                            {{ ticket.atencionPrioritaria?.nombre ?? 'Prioritario' }}
                                        </span>
                                        <span v-else class="text-gray-600">—</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400 font-mono text-xs">
                                        {{ tiempoEspera(ticket) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
