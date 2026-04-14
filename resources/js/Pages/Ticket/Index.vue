<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';

const props = defineProps({
    tramites: {
        type: Array,
        required: true,
    },
});

const tramitesOrdenados = computed(() => {
    const general = props.tramites.find(t => t.nombre.toLowerCase().includes('general'));
    const resto = props.tramites.filter(t => t !== general);
    return general ? [general, ...resto] : props.tramites;
});

const tramiteDefault = props.tramites.find(t => t.nombre.toLowerCase().includes('general'))?.id
    ?? props.tramites[0]?.id
    ?? '';

const form = useForm({
    dni: '',
    tramite_id: tramiteDefault,
    tiene_discapacidad: false,
});

const formKey = ref(0);
const datosExito = ref(null);
const countdown = ref(10);
let countdownInterval = null;

const dniDisplay = computed(() => form.dni || '');

const presionarTecla = (tecla) => {
    if (tecla === 'DEL') {
        form.dni = form.dni.slice(0, -1);
    } else if (form.dni.length < 8) {
        form.dni += tecla;
    }
};

const seleccionarTramite = (id) => {
    form.tramite_id = id;
};

const toggleDiscapacidad = () => {
    form.tiene_discapacidad = !form.tiene_discapacidad;
};

const iniciarCountdown = () => {
    countdown.value = 10;
    countdownInterval = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            regresar();
        }
    }, 1000);
};

const limpiarCountdown = () => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }
};

const enviar = () => {
    form.post(route('ticket.store'), {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash?.numeroTurno) {
                datosExito.value = {
                    numeroTurno: page.props.flash.numeroTurno,
                    dni: page.props.flash.dni,
                    preferencial: form.tiene_discapacidad,
                    tramite: tramitesOrdenados.value.find(t => t.id === form.tramite_id)?.nombre ?? '',
                };
                iniciarCountdown();
            }
            form.reset();
            form.tramite_id = tramiteDefault;
            formKey.value++;
        },
    });
};

const regresar = () => {
    limpiarCountdown();
    datosExito.value = null;
};

onUnmounted(() => limpiarCountdown());

const teclas = ['1','2','3','4','5','6','7','8','9','DEL','0',''];
</script>

<template>
    <Head title="Registro de Turno" />

    <div class="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-teal-700 flex items-center justify-center p-6 select-none">

        <!-- PANTALLA DE ÉXITO -->
        <div v-if="datosExito" class="w-full max-w-2xl text-center">
            <h2 class="text-5xl font-black text-white drop-shadow-lg mb-8">Registrado</h2>

            <div class="bg-white/10 backdrop-blur rounded-3xl p-8 mb-6">
                <p class="text-2xl text-blue-200 mb-4">DNI</p>
                <div class="bg-white/20 rounded-2xl py-6 px-8 flex items-center justify-center">
                    <span class="text-7xl font-black text-white tracking-widest">{{ datosExito.dni }}</span>
                </div>
            </div>

            <div class="bg-teal-400 rounded-2xl py-4 px-8 mb-4">
                <span class="text-2xl font-bold text-white">{{ datosExito.tramite }}</span>
            </div>

            <div v-if="datosExito.preferencial" class="bg-amber-400 rounded-2xl py-4 px-8 mb-6">
                <span class="text-2xl font-black text-amber-900">Preferencial</span>
            </div>

            <p class="text-2xl text-blue-200 mb-10">
                Será llamado pronto. Esté atento a la pantalla.
            </p>

            <button
                @click="regresar"
                class="w-full py-6 rounded-2xl bg-blue-600 text-white text-3xl font-bold transition-all active:scale-95"
            >
                Volver ({{ countdown }}s)
            </button>
        </div>

        <!-- FORMULARIO TÁCTIL -->
        <div v-else class="w-full max-w-5xl" :key="formKey">
            <div class="text-center mb-8">
                <h1 class="text-5xl font-black text-white drop-shadow-lg">Solicitar Turno</h1>
                <p class="text-blue-200 text-2xl mt-2">Toque las opciones para continuar</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                <!-- COLUMNA IZQUIERDA: DNI + Teclado numérico -->
                <div class="bg-white/10 backdrop-blur rounded-3xl p-6">
                    <h2 class="text-white text-2xl font-bold mb-4">1. Ingrese su DNI</h2>

                    <!-- Display DNI -->
                    <div class="bg-white/20 rounded-2xl py-5 px-6 mb-2 text-center min-h-[80px] flex items-center justify-center">
                        <span class="text-5xl font-black text-white tracking-widest">
                            {{ dniDisplay || '––––––––' }}
                        </span>
                    </div>
                    <p class="text-center text-blue-200 text-base mb-3">
                        {{ form.dni.length }}/8 dígitos
                        <span v-if="form.dni.length === 8" class="text-green-300 font-bold"> ✓</span>
                    </p>
                    <p v-if="form.errors.dni" class="text-red-300 text-lg mb-3">{{ form.errors.dni }}</p>

                    <!-- Teclado numérico -->
                    <div class="grid grid-cols-3 gap-3">
                        <template v-for="tecla in teclas" :key="tecla">
                            <button
                                v-if="tecla === 'DEL'"
                                @click="presionarTecla('DEL')"
                                class="col-span-1 py-6 rounded-2xl bg-red-500/70 text-white text-2xl font-bold active:scale-95 transition-all"
                            >
                                ⌫
                            </button>
                            <button
                                v-else-if="tecla !== ''"
                                @click="presionarTecla(tecla)"
                                class="col-span-1 py-6 rounded-2xl bg-white/20 hover:bg-white/30 text-white text-3xl font-bold active:scale-95 transition-all"
                            >
                                {{ tecla }}
                            </button>
                            <div v-else class="col-span-1"></div>
                        </template>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: Trámite + Preferencial -->
                <div class="space-y-6">

                    <!-- SELECCIÓN DE TRÁMITE -->
                    <div class="bg-white/10 backdrop-blur rounded-3xl p-6">
                        <h2 class="text-white text-2xl font-bold mb-4">2. Seleccione el trámite</h2>
                        <div class="grid grid-cols-1 gap-3">
                            <button
                                v-for="tramite in tramitesOrdenados"
                                :key="tramite.id"
                                @click="seleccionarTramite(tramite.id)"
                                :class="[
                                    'w-full py-5 px-6 rounded-2xl text-xl font-semibold text-left transition-all active:scale-95',
                                    form.tramite_id === tramite.id
                                        ? 'bg-teal-400 text-white shadow-lg shadow-teal-900/40'
                                        : 'bg-white/20 text-white hover:bg-white/30'
                                ]"
                            >
                                <span class="flex items-center gap-3">
                                    <span :class="['w-7 h-7 rounded-full border-2 flex-shrink-0 flex items-center justify-center',
                                        form.tramite_id === tramite.id ? 'border-white bg-white' : 'border-white/60']">
                                        <span v-if="form.tramite_id === tramite.id" class="w-3 h-3 rounded-full bg-teal-500"></span>
                                    </span>
                                    {{ tramite.nombre }}
                                </span>
                            </button>
                        </div>
                        <p v-if="form.errors.tramite_id" class="mt-3 text-red-300 text-lg">{{ form.errors.tramite_id }}</p>
                    </div>

                    <!-- TOGGLE PREFERENCIAL -->
                    <button
                        @click="toggleDiscapacidad"
                        :class="[
                            'w-full py-6 px-6 rounded-3xl text-xl font-semibold text-left transition-all active:scale-95',
                            form.tiene_discapacidad
                                ? 'bg-amber-400 text-amber-900 shadow-lg'
                                : 'bg-white/10 text-white hover:bg-white/20'
                        ]"
                    >
                        <span class="flex items-center gap-4">
                            <span :class="['w-14 h-8 rounded-full flex items-center px-1 transition-all',
                                form.tiene_discapacidad ? 'bg-amber-600 justify-end' : 'bg-white/30 justify-start']">
                                <span class="w-6 h-6 rounded-full bg-white shadow"></span>
                            </span>
                            <span class="text-xl font-bold">Preferencial</span>
                        </span>
                    </button>
                </div>
            </div>

            <!-- BOTÓN SOLICITAR TURNO -->
            <button
                @click="enviar"
                :disabled="form.processing || form.dni.length !== 8 || !form.tramite_id"
                class="mt-6 w-full py-7 rounded-2xl bg-blue-600 text-white text-3xl font-black transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed shadow-xl"
            >
                <span v-if="form.processing">Procesando...</span>
                <span v-else>Solicitar Turno</span>
            </button>
        </div>
    </div>
</template>
