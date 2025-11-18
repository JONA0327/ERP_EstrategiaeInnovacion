<section class="bg-white/90 backdrop-blur rounded-2xl shadow-lg border border-blue-100 p-6 sm:p-8">
    <header class="flex items-start gap-4">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 text-blue-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 10-6 0 3 3 0 006 0z" />
            </svg>
        </span>
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Información del perfil</h2>
            <p class="mt-1 text-sm text-gray-600">
                Mantén tus datos personales y de contacto actualizados para recibir notificaciones correctamente.
            </p>
        </div>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-8 space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <x-input-label for="name" value="Nombre completo" class="text-sm font-semibold text-gray-700" />
                <x-text-input
                    id="name"
                    name="name"
                    type="text"
                    class="mt-2"
                    :value="old('name', $user->name)"
                    required
                    autofocus
                    autocomplete="name"
                />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="email" value="Correo electrónico" class="text-sm font-semibold text-gray-700" />
                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    class="mt-2"
                    :value="old('email', $user->email)"
                    required
                    autocomplete="username"
                />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-4 rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                        <p class="text-sm text-yellow-800">
                            Tu correo electrónico aún no ha sido verificado. Revisa tu bandeja de entrada o solicita un nuevo mensaje de verificación.
                        </p>

                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <button form="send-verification" class="inline-flex items-center text-sm font-semibold text-yellow-700 hover:text-yellow-900">
                                Reenviar correo de verificación
                            </button>

                            @if (session('status') === 'verification-link-sent')
                                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                    ¡Correo enviado nuevamente!
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <x-primary-button class="bg-blue-600 hover:bg-blue-700 focus:ring-blue-500">Guardar cambios</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-semibold text-blue-600"
                >
                    Datos actualizados correctamente.
                </p>
            @endif
        </div>
    </form>
</section>

<form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
    @csrf
</form>
