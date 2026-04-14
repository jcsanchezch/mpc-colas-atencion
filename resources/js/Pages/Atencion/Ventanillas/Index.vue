<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps(['ventanillas']);

const form = useForm({ codigo: '', nombre: '' });
const editForm = useForm({ codigo: '', nombre: '' });
const editingId = ref(null);

const createVentanilla = () => {
    form.post(route('ventanillas.store'), {
        onSuccess: () => form.reset(),
    });
};

const startEdit = (ventanilla) => {
    editingId.value = ventanilla.id;
    editForm.codigo = ventanilla.codigo;
    editForm.nombre = ventanilla.nombre;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const updateVentanilla = (id) => {
    editForm.put(route('ventanillas.update', id), {
        onSuccess: () => cancelEdit(),
    });
};

const deleteVentanilla = (id) => {
    if (confirm('¿Está seguro de eliminar esta ventanilla?')) {
        useForm({}).delete(route('ventanillas.destroy', id));
    }
};
</script>

<template>
    <Head title="Ventanillas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-200 leading-tight">Ventanillas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Formulario de creación -->
                <div class="p-4 sm:p-8 bg-gray-900 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-100">Crear Ventanilla</h2>
                            <p class="mt-1 text-sm text-gray-400">Añada una nueva ventanilla al sistema.</p>
                        </header>

                        <form @submit.prevent="createVentanilla" class="mt-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                                <div>
                                    <label for="codigo" class="block font-medium text-sm text-gray-300">Código</label>
                                    <input id="codigo" type="text" v-model="form.codigo" maxlength="10"
                                        class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm uppercase"
                                        required autofocus />
                                    <p class="mt-1 text-xs text-gray-500">Máx. 10 caracteres (ej: V1, CAJA)</p>
                                    <div v-if="form.errors.codigo" class="text-red-500 text-sm mt-1">{{ form.errors.codigo }}</div>
                                </div>
                                <div>
                                    <label for="nombre" class="block font-medium text-sm text-gray-300">Nombre</label>
                                    <input id="nombre" type="text" v-model="form.nombre"
                                        class="mt-1 block w-full bg-gray-800 border-gray-700 text-white rounded-md shadow-sm"
                                        required />
                                    <div v-if="form.errors.nombre" class="text-red-500 text-sm mt-1">{{ form.errors.nombre }}</div>
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
                            <h2 class="text-lg font-medium text-gray-100 mb-6">Lista de Ventanillas</h2>
                        </header>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-300">
                                <thead class="text-xs text-gray-400 uppercase bg-gray-800">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Código</th>
                                        <th scope="col" class="px-6 py-3">Nombre</th>
                                        <th scope="col" class="px-6 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="ventanilla in ventanillas" :key="ventanilla.id"
                                        class="border-b border-gray-800 hover:bg-gray-800/50">

                                        <!-- Modo vista -->
                                        <template v-if="editingId !== ventanilla.id">
                                            <td class="px-6 py-4 font-mono font-medium text-gray-100">{{ ventanilla.codigo }}</td>
                                            <td class="px-6 py-4 text-gray-300">{{ ventanilla.nombre }}</td>
                                            <td class="px-6 py-4 text-right space-x-3">
                                                <button @click="startEdit(ventanilla)" class="text-blue-400 hover:underline">Editar</button>
                                                <button @click="deleteVentanilla(ventanilla.id)" class="text-red-400 hover:underline">Eliminar</button>
                                            </td>
                                        </template>

                                        <!-- Modo edición -->
                                        <template v-else>
                                            <td class="px-6 py-4">
                                                <input type="text" v-model="editForm.codigo" maxlength="10"
                                                    class="bg-gray-700 border-gray-600 text-white rounded px-2 w-24 uppercase font-mono" />
                                                <div v-if="editForm.errors.codigo" class="text-red-500 text-xs mt-1">{{ editForm.errors.codigo }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <input type="text" v-model="editForm.nombre"
                                                    class="bg-gray-700 border-gray-600 text-white rounded px-2 w-full max-w-xs" />
                                                <div v-if="editForm.errors.nombre" class="text-red-500 text-xs mt-1">{{ editForm.errors.nombre }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-right space-x-3">
                                                <button @click="updateVentanilla(ventanilla.id)" :disabled="editForm.processing"
                                                    class="text-emerald-400 hover:underline">Guardar</button>
                                                <button @click="cancelEdit" class="text-gray-400 hover:underline">Cancelar</button>
                                            </td>
                                        </template>
                                    </tr>
                                    <tr v-if="ventanillas.length === 0">
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No hay ventanillas registradas.</td>
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
