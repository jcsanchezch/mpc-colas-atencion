<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps(['tramites']);

const form = useForm({ nombre: '', prefijo: '' });
const editForm = useForm({ nombre: '', prefijo: '' });
const editingId = ref(null);

const createTramite = () => {
    form.post(route('tramites.store'), {
        onSuccess: () => form.reset(),
    });
};

const startEdit = (tramite) => {
    editingId.value = tramite.id;
    editForm.nombre = tramite.nombre;
    editForm.prefijo = tramite.prefijo;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const updateTramite = (id) => {
    editForm.put(route('tramites.update', id), {
        onSuccess: () => cancelEdit(),
    });
};

const deleteTramite = (id) => {
    if (confirm('¿Está seguro de eliminar este trámite?')) {
        useForm({}).delete(route('tramites.destroy', id));
    }
};
</script>

<template>
    <Head title="Trámites" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">Trámites</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Creation Form -->
                <div class="p-4 sm:p-8 bg-gray-900 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-100">Crear Trámite</h2>
                            <p class="mt-1 text-sm text-gray-400">Añada un nuevo trámite al sistema.</p>
                        </header>

                        <form @submit.prevent="createTramite" class="mt-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                                <div>
                                    <label for="nombre" class="block font-medium text-sm text-gray-300">Nombre</label>
                                    <input id="nombre" type="text" v-model="form.nombre" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm" required autofocus />
                                    <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">{{ form.errors.nombre }}</div>
                                </div>
                                <div>
                                    <label for="prefijo" class="block font-medium text-sm text-gray-300">Prefijo</label>
                                    <input id="prefijo" type="text" v-model="form.prefijo" maxlength="2" class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm uppercase" required />
                                    <p class="mt-1 text-xs text-gray-500">Máx. 2 caracteres (ej: A, G)</p>
                                    <div v-if="form.errors.prefijo" class="text-red-500 text-sm mt-1">{{ form.errors.prefijo }}</div>
                                </div>
                            </div>
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 disabled:opacity-25 transition">
                                Guardar
                            </button>
                        </form>
                    </section>
                </div>

                <!-- List / Edit -->
                <div class="p-4 sm:p-8 bg-gray-900 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-100 mb-6">Lista de Trámites</h2>
                        </header>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-300">
                                <thead class="text-xs text-gray-400 uppercase bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Nombre</th>
                                        <th scope="col" class="px-6 py-3">Prefijo</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="tramite in tramites" :key="tramite.id" class="border-b border-gray-800 hover:bg-gray-800/50">
                                        
                                        <!-- View Mode -->
                                        <td v-if="editingId !== tramite.id" class="px-6 py-4 font-medium text-gray-100">{{ tramite.nombre }}</td>
                                        <td v-if="editingId !== tramite.id" class="px-6 py-4 text-gray-300 font-mono">{{ tramite.prefijo }}</td>
                                        <td v-if="editingId !== tramite.id" class="px-6 py-4 text-right space-x-3">
                                            <button @click="startEdit(tramite)" class="text-blue-400 hover:underline">Editar</button>
                                            <button @click="deleteTramite(tramite.id)" class="text-red-400 hover:underline">Eliminar</button>
                                        </td>

                                        <!-- Edit Mode -->
                                        <td v-if="editingId === tramite.id" class="px-6 py-4">
                                            <input type="text" v-model="editForm.nombre" class="bg-gray-700 border-gray-600 text-white rounded px-2 w-full max-w-xs" />
                                            <div v-if="editForm.errors.nombre" class="text-red-500 text-xs mt-1">{{ editForm.errors.nombre }}</div>
                                        </td>
                                        <td v-if="editingId === tramite.id" class="px-6 py-4">
                                            <input type="text" v-model="editForm.prefijo" maxlength="2" class="bg-gray-700 border-gray-600 text-white rounded px-2 w-16 uppercase" />
                                            <div v-if="editForm.errors.prefijo" class="text-red-500 text-xs mt-1">{{ editForm.errors.prefijo }}</div>
                                        </td>
                                        <td v-if="editingId === tramite.id" class="px-6 py-4 text-right space-x-3">
                                            <button @click="updateTramite(tramite.id)" class="text-emerald-400 hover:underline" :disabled="editForm.processing">Guardar</button>
                                            <button @click="cancelEdit" class="text-gray-400 hover:underline">Cancelar</button>
                                        </td>
                                    </tr>
                                    <tr v-if="tramites.length === 0">
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No hay trámites registrados.</td>
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
