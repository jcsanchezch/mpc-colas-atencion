<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps(['dias']);

const form = useForm({ fecha: '', hora_inicio: '', hora_fin: '' });
const editForm = useForm({ fecha: '', hora_inicio: '', hora_fin: '', activo: true });
const editingId = ref(null);

const createDia = () => {
    form.post(route('dias.store'), {
        onSuccess: () => form.reset(),
    });
};

const startEdit = (dia) => {
    editingId.value = dia.id;
    editForm.fecha = dia.fecha;
    editForm.hora_inicio = dia.hora_inicio;
    editForm.hora_fin = dia.hora_fin;
    editForm.activo = dia.activo;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const updateDia = (id) => {
    editForm.put(route('dias.update', id), {
        onSuccess: () => cancelEdit(),
    });
};

const deleteDia = (id) => {
    if (confirm('¿Está seguro de eliminar este día?')) {
        useForm({}).delete(route('dias.destroy', id));
    }
};

const formatFecha = (fecha) => {
    if (!fecha) return '';
    const [y, m, d] = fecha.split('-');
    return `${d}/${m}/${y}`;
};
</script>

<template>
    <Head title="Días de Atención" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">Días de Atención</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Formulario de creación -->
                <div class="p-4 sm:p-8 bg-gray-900 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-100">Crear Día de Atención</h2>
                            <p class="mt-1 text-sm text-gray-400">Registre un nuevo día de atención al público.</p>
                        </header>

                        <form @submit.prevent="createDia" class="mt-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-3xl">
                                <div>
                                    <label for="fecha" class="block font-medium text-sm text-gray-300">Fecha</label>
                                    <input id="fecha" type="date" v-model="form.fecha"
                                        class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm"
                                        required autofocus />
                                    <div v-if="form.errors.fecha" class="text-red-500 text-sm mt-1">{{ form.errors.fecha }}</div>
                                </div>
                                <div>
                                    <label for="hora_inicio" class="block font-medium text-sm text-gray-300">Hora Inicio</label>
                                    <input id="hora_inicio" type="time" v-model="form.hora_inicio"
                                        class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm"
                                        required />
                                    <div v-if="form.errors.hora_inicio" class="text-red-500 text-sm mt-1">{{ form.errors.hora_inicio }}</div>
                                </div>
                                <div>
                                    <label for="hora_fin" class="block font-medium text-sm text-gray-300">Hora Fin</label>
                                    <input id="hora_fin" type="time" v-model="form.hora_fin"
                                        class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm"
                                        required />
                                    <div v-if="form.errors.hora_fin" class="text-red-500 text-sm mt-1">{{ form.errors.hora_fin }}</div>
                                </div>
                            </div>
                            <button type="submit" :disabled="form.processing"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 disabled:opacity-25 transition">
                                Guardar
                            </button>
                        </form>
                    </section>
                </div>

                <!-- Lista -->
                <div class="p-4 sm:p-8 bg-gray-900 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-100 mb-6">Lista de Días</h2>
                        </header>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-300">
                                <thead class="text-xs text-gray-400 uppercase bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Fecha</th>
                                        <th scope="col" class="px-6 py-3">Hora Inicio</th>
                                        <th scope="col" class="px-6 py-3">Hora Fin</th>
                                        <th scope="col" class="px-6 py-3 text-center">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-center">Tickets</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="dia in dias" :key="dia.id"
                                        class="border-b border-gray-800 hover:bg-gray-800/50">

                                        <!-- Modo vista -->
                                        <template v-if="editingId !== dia.id">
                                            <td class="px-6 py-4 font-medium text-gray-100 font-mono">{{ formatFecha(dia.fecha) }}</td>
                                            <td class="px-6 py-4 font-mono">{{ dia.hora_inicio }}</td>
                                            <td class="px-6 py-4 font-mono">{{ dia.hora_fin }}</td>
                                            <td class="px-6 py-4 text-center">
                                                <span :class="dia.activo ? 'bg-emerald-900 text-emerald-300' : 'bg-gray-700 text-gray-400'"
                                                    class="text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                    {{ dia.activo ? 'Activo' : 'Cerrado' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-center text-gray-400">{{ dia.contador }}</td>
                                            <td class="px-6 py-4 text-right space-x-3">
                                                <button @click="startEdit(dia)" class="text-blue-400 hover:underline">Editar</button>
                                                <button @click="deleteDia(dia.id)" class="text-red-400 hover:underline">Eliminar</button>
                                            </td>
                                        </template>

                                        <!-- Modo edición -->
                                        <template v-else>
                                            <td class="px-6 py-4">
                                                <input type="date" v-model="editForm.fecha"
                                                    class="bg-gray-700 border-gray-600 text-white rounded px-2 w-36" />
                                                <div v-if="editForm.errors.fecha" class="text-red-500 text-xs mt-1">{{ editForm.errors.fecha }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="time" v-model="editForm.hora_inicio"
                                                    class="bg-gray-700 border-gray-600 text-white rounded px-2 w-28" />
                                                <div v-if="editForm.errors.hora_inicio" class="text-red-500 text-xs mt-1">{{ editForm.errors.hora_inicio }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="time" v-model="editForm.hora_fin"
                                                    class="bg-gray-700 border-gray-600 text-white rounded px-2 w-28" />
                                                <div v-if="editForm.errors.hora_fin" class="text-red-500 text-xs mt-1">{{ editForm.errors.hora_fin }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" v-model="editForm.activo"
                                                        class="rounded border-gray-600 bg-gray-700 text-blue-500" />
                                                    <span class="text-gray-300 text-xs">Activo</span>
                                                </label>
                                            </td>
                                            <td class="px-6 py-4 text-center text-gray-500">—</td>
                                            <td class="px-6 py-4 text-right space-x-3">
                                                <button @click="updateDia(dia.id)" :disabled="editForm.processing"
                                                    class="text-emerald-400 hover:underline">Guardar</button>
                                                <button @click="cancelEdit" class="text-gray-400 hover:underline">Cancelar</button>
                                            </td>
                                        </template>
                                    </tr>
                                    <tr v-if="dias.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay días registrados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
