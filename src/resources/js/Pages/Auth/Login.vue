<template>
    <div>
        <!-- Cabeçalho -->
        <div style="text-align:center; margin-bottom:2rem;">
            <div style="display:inline-flex; align-items:center; gap:0.5rem; margin-bottom:0.5rem;">
                <i class="pi pi-box" style="font-size:1.75rem; color:#0d9488;" />
                <span style="font-size:1.75rem; font-weight:800; color:#0d9488; letter-spacing:-0.02em;">ERP</span>
            </div>
            <p style="color:#64748b; font-size:0.875rem; margin:0;">Sistema de Gestão</p>
        </div>

        <!-- Formulário -->
        <form @submit.prevent="submit">
            <!-- Email -->
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem; color:#374151;">
                    E-mail
                </label>
                <InputText
                    v-model="form.email"
                    type="email"
                    placeholder="seu@email.com"
                    autocomplete="email"
                    :invalid="!!form.errors.email"
                    style="width:100%;"
                />
                <small v-if="form.errors.email" style="color:#ef4444; font-size:0.75rem; margin-top:0.25rem; display:block;">
                    {{ form.errors.email }}
                </small>
            </div>

            <!-- Senha -->
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-size:0.8125rem; font-weight:500; margin-bottom:0.375rem; color:#374151;">
                    Senha
                </label>
                <Password
                    v-model="form.password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    :feedback="false"
                    toggle-mask
                    :invalid="!!form.errors.password"
                    style="width:100%;"
                    input-style="width:100%;"
                    @keyup.enter="submit"
                />
                <small v-if="form.errors.password" style="color:#ef4444; font-size:0.75rem; margin-top:0.25rem; display:block;">
                    {{ form.errors.password }}
                </small>
            </div>

            <!-- Lembrar-me -->
            <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:1.5rem;">
                <Checkbox v-model="form.remember" :binary="true" input-id="remember" />
                <label for="remember" style="font-size:0.8125rem; color:#374151; cursor:pointer;">
                    Lembrar-me
                </label>
            </div>

            <!-- Botão -->
            <Button
                type="submit"
                label="Entrar"
                icon="pi pi-sign-in"
                :loading="form.processing"
                style="width:100%;"
            />
        </form>

        <!-- Esqueci a senha -->
        <div style="text-align:center; margin-top:1.25rem;">
            <a href="#" style="font-size:0.8125rem; color:#0d9488; text-decoration:none;">
                Esqueci minha senha
            </a>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'

defineOptions({ layout: GuestLayout })

const form = useForm({
    email:    '',
    password: '',
    remember: false,
})

function submit() {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>
