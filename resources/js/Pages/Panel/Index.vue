<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import logoUrl from '../../../images/logo-blanco-horizontal-fondo-transparente.png';

// ── Props desde el servidor ───────────────────────────────────────────────────
const props = defineProps({
    initialActiveTickets:    Array,
    initialCompletedTickets: Array,
    initialAbandonedTickets: Array,
    initialPendingTickets:   Array,
});

// ── Estado reactivo ───────────────────────────────────────────────────────────
const turnosActivos     = ref([...props.initialActiveTickets]);
const turnosAtendidos   = ref([...props.initialCompletedTickets]);
const turnosAbandonados = ref([...props.initialAbandonedTickets]);
const turnosPendientes  = ref([...props.initialPendingTickets]);

const turnosLlamando   = computed(() => turnosActivos.value.filter(t => t.estado === 'LLAMANDO'));
const turnosAtendiendo = computed(() => turnosActivos.value.filter(t => t.estado === 'ATENDIENDO'));

// Orden: llamando → en atención → pendientes → historial (más reciente)
const todosTurnos = computed(() => {
    const historial = [...turnosAtendidos.value, ...turnosAbandonados.value];
    historial.sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at));
    return [
        ...turnosLlamando.value,
        ...turnosAtendiendo.value,
        ...turnosPendientes.value,
        ...historial,
    ];
});

// ── Reloj en vivo ─────────────────────────────────────────────────────────────
const ahora = ref(new Date());
let temporizadorReloj = null;
onMounted(() => {
    temporizadorReloj = setInterval(() => { ahora.value = new Date(); }, 1000);
});
onUnmounted(() => clearInterval(temporizadorReloj));

const horaTexto = computed(() =>
    ahora.value.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })
);
const fechaTexto = computed(() =>
    ahora.value.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: '2-digit' })
);

// ── WebSocket (Laravel Echo) ──────────────────────────────────────────────────
onMounted(() => {
    if (!window.Echo) return;

    window.Echo.channel('public.tickets')
        .listen('TicketCreated', (evento) => {
            const turno = evento.ticket;
            if (!turnosPendientes.value.find(t => t.id === turno.id)) {
                turnosPendientes.value.push(turno);
            }
        })
        .listen('TicketCalled', (evento) => {
            sonarCampana();
            const turno = evento.ticket;

            // Quitar de pendientes, atendidos y abandonados
            turnosPendientes.value  = turnosPendientes.value.filter(t => t.id !== turno.id);
            turnosAtendidos.value   = turnosAtendidos.value.filter(t => t.id !== turno.id);
            turnosAbandonados.value = turnosAbandonados.value.filter(t => t.id !== turno.id);

            const posicion = turnosActivos.value.findIndex(t => t.id === turno.id);
            if (posicion > -1) {
                turnosActivos.value[posicion] = turno;
            } else {
                turnosActivos.value.unshift(turno);
                if (turnosActivos.value.length > 10) turnosActivos.value.pop();
            }
        })
        .listen('TicketStatusUpdated', (evento) => {
            const turno = evento.ticket;

            if (turno.estado === 'ATENDIDO') {
                turnosActivos.value = turnosActivos.value.filter(t => t.id !== turno.id);
                if (!turnosAtendidos.value.find(t => t.id === turno.id)) {
                    turnosAtendidos.value.unshift(turno);
                    if (turnosAtendidos.value.length > 20) turnosAtendidos.value.pop();
                }
            } else if (turno.estado === 'ABANDONADO') {
                turnosActivos.value = turnosActivos.value.filter(t => t.id !== turno.id);
                if (!turnosAbandonados.value.find(t => t.id === turno.id)) {
                    turnosAbandonados.value.unshift(turno);
                    if (turnosAbandonados.value.length > 20) turnosAbandonados.value.pop();
                }
            } else {
                const posicion = turnosActivos.value.findIndex(t => t.id === turno.id);
                if (posicion > -1) turnosActivos.value[posicion] = turno;
            }
        });
});

// ── Helpers ───────────────────────────────────────────────────────────────────
const numeroVentanilla = (nombre) => nombre ? nombre.replace(/^ventanilla\s*/i, '') : '—';

// ── Sonido de campana ─────────────────────────────────────────────────────────
const sonarCampana = () => {
    try {
        const contexto  = new (window.AudioContext || window.webkitAudioContext)();
        const oscilador = contexto.createOscillator();
        const volumen   = contexto.createGain();
        oscilador.type = 'sine';
        oscilador.frequency.setValueAtTime(880, contexto.currentTime);
        volumen.gain.setValueAtTime(0.15, contexto.currentTime);
        volumen.gain.exponentialRampToValueAtTime(0.001, contexto.currentTime + 0.6);
        oscilador.connect(volumen);
        volumen.connect(contexto.destination);
        oscilador.start();
        oscilador.stop(contexto.currentTime + 0.6);
    } catch (error) {
        console.warn('Audio no disponible', error);
    }
};
</script>

<template>
    <Head title="Panel de Turnos" />

    <div class="min-h-screen bg-gray-950 text-white flex flex-col font-sans select-none">

        <!-- ══ FILA 1: ENCABEZADO ══════════════════════════════════════════ -->
        <header class="bg-gray-900 border-b border-gray-800 px-8 py-3 grid grid-cols-3 items-center shadow-lg shrink-0">
            <!-- Columna izquierda: Logo -->
            <div class="flex items-center">
                <img :src="logoUrl" alt="Logo" class="h-14 w-auto object-contain" />
            </div>

            <!-- Columna central: Hora -->
            <div class="flex justify-center">
                <div class="text-6xl font-bold text-white tabular-nums tracking-wider leading-none">
                    {{ horaTexto }}
                </div>
            </div>

            <!-- Columna derecha: Fecha corta -->
            <div class="flex justify-end">
                <div class="text-4xl font-bold text-gray-300 tabular-nums tracking-wider leading-none">
                    {{ fechaTexto }}
                </div>
            </div>
        </header>

        <!-- ══ GRID ÚNICO DE TURNOS ═══════════════════════════════════════ -->
        <section class="flex-1 overflow-y-auto p-3 custom-scrollbar">

            <div v-if="todosTurnos.length === 0"
                 class="h-full flex items-center justify-center text-gray-700 text-xl">
                Sin turnos registrados
            </div>

            <div v-else class="flex flex-wrap">
                <div v-for="turno in todosTurnos"
                     :key="turno.id"
                     class="w-1/4 p-2">

                    <!-- ROJO: llamando -->
                    <div v-if="turno.estado === 'LLAMANDO'"
                         class="rounded-2xl bg-red-950 border-2 border-red-500 shadow-[0_0_28px_rgba(239,68,68,0.45)] animate-pulse-border-red px-6 py-5">
                        <div class="flex items-center justify-center gap-4">
                            <span class="text-5xl font-black text-white tracking-widest leading-none">{{ turno.dni }}</span>
                            <svg v-if="turno.tiene_discapacidad" xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-yellow-300 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>
                            </svg>
                            <span class="text-3xl text-red-400 font-black leading-none">→</span>
                            <span class="text-5xl font-black text-yellow-300 leading-none">{{ numeroVentanilla(turno.ventanilla) }}</span>
                        </div>
                    </div>

                    <!-- AZUL: esperando -->
                    <div v-else-if="turno.estado === 'ESPERANDO'"
                         class="rounded-xl bg-blue-950/70 border border-blue-800 px-5 py-4 flex items-center">
                        <span class="text-5xl font-black text-blue-100 tracking-wider truncate">{{ turno.dni }}</span>
                        <svg v-if="turno.tiene_discapacidad" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-yellow-300 shrink-0 ml-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>
                        </svg>
                    </div>

                    <!-- VERDE: atendiendo, atendido o abandonado -->
                    <div v-else
                         class="rounded-xl bg-green-950/70 border border-green-800 px-5 py-4 flex items-center">
                        <span class="text-5xl font-black text-green-100 tracking-wider truncate">{{ turno.dni }}</span>
                        <svg v-if="turno.tiene_discapacidad" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-yellow-300 shrink-0 ml-2" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/>
                        </svg>
                        <span class="text-5xl text-green-400 font-black shrink-0 ml-3">→</span>
                        <span class="text-5xl font-black text-yellow-400 shrink-0 ml-3">{{ numeroVentanilla(turno.ventanilla) }}</span>
                    </div>

                </div>
            </div>
        </section>

    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }

@keyframes pulso-borde-rojo {
    0%, 100% { box-shadow: 0 0 24px rgba(239, 68, 68, 0.4); }
    50%       { box-shadow: 0 0 52px rgba(239, 68, 68, 0.8); }
}
.animate-pulse-border-red { animation: pulso-borde-rojo 1.5s ease-in-out infinite; }
</style>
