<section class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100 p-6 sm:p-8">
    <header class="flex items-start gap-4">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c.414 0 .75-.336.75-.75v-1.5a.75.75 0 00-1.5 0v1.5c0 .414.336.75.75.75z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.25 11.75a5.25 5.25 0 10-10.5 0v1.5a2.25 2.25 0 00-1.5 2.122v3.878A2.25 2.25 0 007.5 21.5h9a2.25 2.25 0 002.25-2.25v-3.878a2.25 2.25 0 00-1.5-2.122v-1.5z" />
            </svg>
        </span>
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Actualizar contraseña</h2>
            <p class="mt-1 text-sm text-gray-600">Crea una contraseña segura para proteger tu cuenta y tus tickets.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-8 space-y-6">
        @csrf
        @method('put')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <x-input-label for="update_password_current_password" value="Contraseña actual" class="text-sm font-semibold text-gray-700" />
                <x-text-input
                    id="update_password_current_password"
                    name="current_password"
                    type="password"
                    class="mt-2"
                    autocomplete="current-password"
                />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" value="Nueva contraseña" class="text-sm font-semibold text-gray-700" />
                <x-text-input
                    id="update_password_password"
                    name="password"
                    type="password"
                    class="mt-2"
                    autocomplete="new-password"
                />
                <p class="mt-2 text-xs text-gray-500">Utiliza al menos 8 caracteres combinando letras, números y símbolos.</p>
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" value="Confirmar contraseña" class="text-sm font-semibold text-gray-700" />
                <x-text-input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="mt-2"
                    autocomplete="new-password"
                />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button class="bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500">Actualizar contraseña</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-semibold text-indigo-600"
                >
                    ¡Contraseña actualizada con éxito!
                </p>
            @endif
        </div>
    </form>
</section>
