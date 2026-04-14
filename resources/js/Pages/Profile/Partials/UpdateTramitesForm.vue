<script setup>
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    tramites: {
        type: Array,
        required: true
    },
    userTramites: {
        type: Array,
        required: true
    },
    ventanillas: {
        type: Array,
        required: true
    },
    userVentanillaId: {
        type: Number,
        default: null
    }
});

const form = useForm({
    tramites: [...props.userTramites],
    ventanilla_id: props.userVentanillaId
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Mis Trámites a Atender
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Selecciona los tipos de trámites que estás dispuesto a atender en tu ventanilla.
            </p>
        </header>

        <form @submit.prevent="form.post(route('profile.tramites'))" class="mt-6 space-y-6">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label v-for="tramite in tramites" :key="tramite.id" class="flex items-center">
                    <input 
                        type="checkbox" 
                        :value="tramite.id" 
                        v-model="form.tramites"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    >
                    <span class="ml-2 text-sm text-gray-900 dark:text-gray-100">{{ tramite.nombre }}</span>
                </label>
            </div>

            <InputError :message="form.errors.tramites" class="mt-2" />

            <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                <label for="ventanilla_id" class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                    Ventanilla Asignada
                </label>
                <select id="ventanilla_id" v-model="form.ventanilla_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                    <option :value="null">Ninguna / Mesa general</option>
                    <option v-for="ventanilla in ventanillas" :key="ventanilla.id" :value="ventanilla.id">
                        {{ ventanilla.nombre }}
                    </option>
                </select>
                <InputError :message="form.errors.ventanilla_id" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Guardar Trámites</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful === true"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Guardado.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
