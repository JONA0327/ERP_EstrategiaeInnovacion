<section class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-red-100 p-6 sm:p-8 space-y-6">
    <header class="flex items-start gap-4">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-red-100 text-red-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M5.455 5.455l13.09 13.09M19.071 4.929A10 10 0 104.93 19.07 10 10 0 0019.07 4.93z" />
            </svg>
        </span>
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Eliminar cuenta</h2>
            <p class="mt-1 text-sm text-gray-600">Esta acción es permanente. Todos tus tickets y datos asociados se eliminarán.</p>
        </div>
    </header>

    <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
        Asegúrate de haber descargado cualquier información importante antes de continuar.
    </div>

    <x-danger-button
        x-data="{}"
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-5 py-3 bg-red-600 hover:bg-red-700 focus:ring-red-500"
    >
        Eliminar mi cuenta
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('delete')

            <div class="flex items-start gap-4">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-red-100 text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">¿Seguro que deseas eliminar tu cuenta?</h2>
                    <p class="mt-1 text-sm text-gray-600">Una vez eliminada, no podrás recuperar tus tickets ni el historial de soporte.</p>
                </div>
            </div>

            <div>
                <x-input-label for="password" value="Confirma tu contraseña" class="text-sm font-semibold text-gray-700" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-2"
                    placeholder="Ingresa tu contraseña"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')" class="px-5 py-3">Cancelar</x-secondary-button>
                <x-danger-button class="px-5 py-3 bg-red-600 hover:bg-red-700 focus:ring-red-500">Eliminar definitivamente</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
