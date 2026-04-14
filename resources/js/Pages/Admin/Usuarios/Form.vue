<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
    roles: Array,
    tramites: Array,
    ventanillas: Array
});

const form = useForm({
    name: props.user?.name ?? '',
    email: props.user?.email ?? '',
    password: '',
    ventanilla_id: props.user?.ventanilla_id ?? '',
    role: props.user?.roles?.[0]?.name ?? 'clerk',
    tramites: props.user?.tramites?.map(p => p.id) ?? []
});

const submit = () => {
    if (props.user) {
        form.put(route('users.update', props.user.id));
    } else {
        form.post(route('users.store'));
    }
};
</script>

<template>
    <Head :title="user ? 'Editar Usuario' : 'Crear Usuario'" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ user ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg max-w-2xl mx-auto">
                    <form @submit.prevent="submit" class="p-6">
                        
                        <!-- Name -->
                        <div class="mb-4">
                            <InputLabel for="name" value="Nombre del Usuario" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <InputLabel for="email" value="Correo Electrónico" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                v-model="form.email"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <InputLabel for="password" :value="user ? 'Contraseña (dejar en blanco para no cambiar)' : 'Contraseña'" />
                            <TextInput
                                id="password"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password"
                                :required="!user"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <!-- Role -->
                        <div class="mb-4">
                            <InputLabel for="role" value="Rol en el Sistema" />
                            <select 
                                id="role" 
                                v-model="form.role" 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                required
                            >
                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                    {{ role.name.toUpperCase() }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.role" />
                        </div>

                        <!-- Ventanilla -->
                        <div class="mb-4">
                            <InputLabel for="ventanilla_id" value="Ventanilla" />
                            <select 
                                id="ventanilla_id" 
                                v-model="form.ventanilla_id" 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                            >
                                <option value="">Sin Ventanilla</option>
                                <option v-for="ventanilla in ventanillas" :key="ventanilla.id" :value="ventanilla.id">
                                    {{ ventanilla.nombre }}
                                </option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.ventanilla_id" />
                        </div>

                        <!-- Procedures (Trámites permitidos) -->
                        <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Trámites que puede atender:</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label v-for="tramite in tramites" :key="tramite.id" class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        :value="tramite.id" 
                                        v-model="form.tramites"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    >
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ tramite.nombre }}</span>
                                </label>
                            </div>
                            <InputError class="mt-2" :message="form.errors.tramites" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <Link :href="route('users.index')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none mr-4">
                                Cancelar
                            </Link>

                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Guardar Usuario
                            </PrimaryButton>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
