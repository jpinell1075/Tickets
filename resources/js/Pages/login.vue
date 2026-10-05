// This script handles the login page.
// It sets up a form for user authentication and displays error messages if login fails.
// The template includes a form for entering username and password, styled with a centered layout.
<script setup>
    import { useForm } from '@inertiajs/vue3';
    import { computed, ref } from 'vue';
    import { usePage } from '@inertiajs/vue3';
    import Logo from './components/Logo.vue';
    import PremonishHandler from './components/accesibility/PremonishHandler.vue';
    import TemeHandler from './components/accesibility/TemeHandler.vue';

    const form = useForm({
        name: '',
        password: '',
    });

    const errorMessage = computed(() => usePage().props.flash.error?.message ?? '');
    const global_use_social_login = ref(usePage().props.global_use_social_login);

    const submit = () => {
        form.post('/login');
    };
</script>

<template>
    <PremonishHandler />
    <TemeHandler />
    <div id="login-container" class="min-h-screen flex justify-center pt-12">
        <div class="flex justify-center max-w-lg w-full px-4">
            <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg flex flex-col items-center p-6 w-full h-fit border border-transparent [[data-theme=dark-hc]_&]:border-white">
                <Logo :with="'300'" :class="'mt-12'"/>
                <h2 class="mt-12 p-4 pl-0 text-2xl font-bold text-gray-900 dark:text-white" id="title">Iniciar Sesión</h2>

                <h4 v-if="errorMessage" class="text-red-500 font-medium">{{ errorMessage }}</h4>

                <div class="w-full">
                    <form class="mt-6" @submit.prevent="submit">
                        <div class="flex flex-col gap-4">
                            <div class="w-full">
                                <label for="name" class="block font-bold text-gray-700 dark:text-gray-300">Usuario</label>
                                <input v-model="form.name" type="text" id="name" class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500 disabled:opacity-50" required :disabled="form.processing">
                            </div>
                            <div class="w-full">
                                <label for="password" class="block font-bold text-gray-700 dark:text-gray-300">Contraseña</label>
                                <input v-model="form.password" type="password" id="password" class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-green-500 focus:ring-green-500 disabled:opacity-50" required :disabled="form.processing">
                            </div>
                        </div>
                        <div class="mt-10">
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition disabled:opacity-50" type="submit" :disabled="form.processing">
                                Entrar
                            </button>
                        </div>
                    </form>
                    <div class="mt-6" v-if="global_use_social_login">
                        <h5 class="text-center text-lg text-gray-700 dark:text-gray-300">O Iniciar Sesión Con</h5>
                        <div class="flex justify-center items-center gap-2 mt-4">
                            <a :href="route('socialAuth.redirect', {provider: 'google', state: 'login'})"
                                class="bg-red-600 hover:bg-red-700 text-white p-2 rounded transition flex items-center justify-center"><i class='bx bxl-google text-xl'></i></a>
                            <a :href="route('socialAuth.redirect', {provider: 'facebook', state: 'login'})"
                                class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded transition flex items-center justify-center"><i class='bx bxl-facebook-square text-xl'></i></a>
                            <a :href="route('socialAuth.redirect', {provider: 'github', state: 'login'})"
                                class="bg-gray-800 hover:bg-gray-900 text-white p-2 rounded transition flex items-center justify-center"><i class='bx bxl-github text-xl'></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>