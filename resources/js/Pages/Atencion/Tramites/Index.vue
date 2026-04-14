<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps(['tramites']);

const form = useForm({ nombre: '' });
const editForm = useForm({ nombre: '', activo: true });
const editingId = ref(null);

const createTramite = () => {
    form.post(route('tramites.store'), {
        onSuccess: () => form.reset(),
    });
};

const startEdit = (tramite) => {
    editingId.value = tramite.id;
    editForm.nombre = tramite.nombre;
    editForm.activo = tramite.activo;
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

                <!-- Formulario de creación -->
                <div class="p-4 sm:p-8 bg-gray-900 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-100">Crear Trámite</h2>
                            <p class="mt-1 text-sm text-gray-400">Añada un nuevo trámite al sistema.</p>
                        </header>

                        <form @submit.prevent="createTramite" class="mt-6 space-y-6">
                            <div class="max-w-xl">
                                <label for="nombre" class="block font-medium text-sm text-gray-300">Nombre</label>
                                <input id="nombre" type="text" v-model="form.nombre"
                                    class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm"
                                    required autofocus />
                                <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">{{ form.errors.nombre }}</div>
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
                            <h2 class="text-lg font-medium text-gray-100 mb-6">Lista de Trámites</h2>
                        </header>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-300">
                                <thead class="text-xs text-gray-400 uppercase bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-center">Estado</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="tramite in tramites" :key="tramite.id"
                                        class="border-b border-gray-800 hover:bg-gray-800/50">

                                        <!-- Modo vista -->
                                        <template v-if="editingId !== tramite.id">
                                            <td class="px-6 py-4 font-medium text-gray-100">{{ tramite.nombre }}</td>
                                            <td class="px-6 py-4 text-center">
                                                <span :class="tramite.activo ? 'bg-emerald-900 text-emerald-300' : 'bg-gray-700 text-gray-400'"
                                                    class="text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                    {{ tramite.activo ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right space-x-3">
                                                <button @click="startEdit(tramite)" class="text-blue-400 hover:underline">Editar</button>
                                                <button @click="deleteTramite(tramite.id)" class="text-red-400 hover:underline">Eliminar</button>
                                            </td>
                                        </template>

                                        <!-- Modo edición -->
                                        <template v-else>
                                            <td class="px-6 py-4">
                                                <input type="text" v-model="editForm.nombre"
                                                    class="bg-gray-700 border-gray-600 text-white rounded px-2 w-full max-w-xs" />
                                                <div v-if="editForm.errors.nombre" class="text-red-500 text-xs mt-1">{{ editForm.errors.nombre }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" v-model="editForm.activo" class="rounded border-gray-600 bg-gray-700 text-blue-500" />
                                                    <span class="text-gray-300 text-xs">Activo</span>
                                                </label>
                                            </td>
                                            <td class="px-6 py-4 text-right space-x-3">
                                                <button @click="updateTramite(tramite.id)" :disabled="editForm.processing"
                                                    class="text-emerald-400 hover:underline">Guardar</button>
                                                <button @click="cancelEdit" class="text-gray-400 hover:underline">Cancelar</button>
                                            </td>
                                        </template>
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
