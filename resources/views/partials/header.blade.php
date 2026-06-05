<header class="sticky top-0 z-20 flex items-center justify-between px-4 py-3 bg-white border-b shadow-sm border-slate-200 md:px-6 md:py-4">
    
    {{-- Título y botón móvil --}}
    <div class="flex items-center gap-3">
        {{-- Botón para móvil (toggle sidebar) --}}
        <button @click="toggleMobileSidebar()" 
                class="p-2 transition-colors rounded-lg md:hidden hover:bg-slate-100">
            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        
        <div>
            <h2 class="text-xl font-bold text-slate-800">
                @yield('page-title', 'Dashboard')
            </h2>
            <p class="text-xs text-slate-400 mt-0.5 hidden md:block">
                @yield('breadcrumb', 'Panel de control')
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2 md:gap-4">

        {{-- SELECTOR DE EMPRESA (SOLO SUPER ADMIN) --}}
        @if(auth()->user()->hasRole('Super Admin'))
        <div class="relative" x-data="{ openEmpresa: false }">
            <button @click="openEmpresa = !openEmpresa"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium transition md:px-4 bg-slate-100 rounded-xl hover:bg-slate-200">
                <span class="text-base">🏢</span>
                <span class="hidden sm:inline">{{ Str::limit(session('empresa_activa_nombre', auth()->user()->empresa->nombre ?? 'Sin empresa'), 20) }}</span>
                <svg class="hidden w-4 h-4 sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <div x-show="openEmpresa" 
                 x-cloak
                 @click.away="openEmpresa = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="absolute right-0 z-50 py-2 mt-2 overflow-y-auto bg-white border shadow-xl w-80 rounded-2xl max-h-96">

                <div class="sticky top-0 px-4 py-2 bg-white border-b">
                    <p class="text-xs font-medium text-gray-400 uppercase">Seleccionar empresa</p>
                </div>

                @php
                    $empresas = \App\Models\Empresa::with('licencia')->orderBy('nombre')->get();
                    $empresaActivaId = session('empresa_activa_id', auth()->user()->empresa_id);
                @endphp

                @foreach($empresas as $emp)
                    <a href="{{ route('empresa.cambiar', $emp) }}"
                        class="flex items-center justify-between px-4 py-3 hover:bg-indigo-50 transition {{ $empresaActivaId == $emp->id ? 'bg-indigo-50 border-l-4 border-indigo-600' : '' }}">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $emp->nombre }}</p>
                            <p class="text-xs text-gray-400">{{ $emp->licencia->nombre ?? 'Sin licencia' }} · {{ $emp->sucursales->count() }} suc.</p>
                        </div>
                        @if($empresaActivaId == $emp->id)
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        @endif
                    </a>
                @endforeach

                <div class="sticky bottom-0 px-4 py-2 mt-2 bg-white border-t">
                    <a href="{{ route('empresas.create') }}"
                        class="flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Registrar nueva empresa
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- SELECTOR DE SUCURSAL --}}
        @php
            $empresaId = session('empresa_activa_id', auth()->user()->empresa_id);
            $sucursales = \App\Models\Sucursal::where('empresa_id', $empresaId)
                ->where('activo', true)
                ->get();
            
            $sucursalActivaId = session('sucursal_activa_id', auth()->user()->sucursal_id);
            $sucursalActiva = $sucursales->firstWhere('id', $sucursalActivaId) ?? $sucursales->first();
        @endphp

        <div class="relative" x-data="{ openSucursal: false }">
            <button @click="openSucursal = !openSucursal"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium transition md:px-4 bg-slate-100 rounded-xl hover:bg-slate-200">
                <span class="text-base">📍</span>
                <span class="hidden sm:inline">{{ Str::limit($sucursalActiva->nombre ?? 'Sin sucursal', 20) }}</span>
                @if($sucursales->count() > 1 || auth()->user()->hasRole('Super Admin'))
                <svg class="hidden w-4 h-4 sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
                @endif
            </button>

            <div x-show="openSucursal" 
                 x-cloak
                 @click.away="openSucursal = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="absolute right-0 z-50 py-2 mt-2 bg-white border shadow-xl w-72 rounded-2xl">

                <div class="px-4 py-2 border-b">
                    <p class="text-xs font-medium text-gray-400 uppercase">Sucursales disponibles</p>
                </div>

                @if($sucursales->count() > 0)
                    @foreach($sucursales as $suc)
                        <a href="{{ route('sucursal.cambiar', $suc) }}"
                            class="flex items-center justify-between px-4 py-3 hover:bg-indigo-50 transition {{ $sucursalActivaId == $suc->id ? 'bg-indigo-50' : '' }}">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📍</span>
                                <span class="text-sm {{ $sucursalActivaId == $suc->id ? 'font-medium text-indigo-700' : 'text-slate-700' }}">
                                    {{ $suc->nombre }}
                                </span>
                            </div>
                            @if($sucursalActivaId == $suc->id)
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </a>
                    @endforeach
                @else
                    <p class="px-4 py-3 text-sm text-center text-gray-400">No hay sucursales</p>
                @endif

                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Administrador'))
                <div class="px-4 py-2 mt-2 border-t">
                    <a href="{{ route('sucursales.create', ['empresa_id' => $empresaId]) }}"
                        class="flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nueva sucursal
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- DROPDOWN DE USUARIO SIMPLIFICADO --}}
        <div class="relative" x-data="{ openUser: false }">
            <button @click="openUser = !openUser"
                    class="flex items-center gap-2 p-2 transition-all duration-200 hover:bg-slate-100 rounded-xl">
                {{-- Solo el ícono del usuario --}}
                <div class="flex items-center justify-center font-bold text-white rounded-full shadow-md w-9 h-9 bg-gradient-to-br from-indigo-600 to-cyan-500">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </button>

            {{-- Dropdown de usuario simplificado --}}
            <div x-show="openUser" 
                 x-cloak
                 @click.away="openUser = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="absolute right-0 z-50 mt-3 overflow-hidden bg-white border shadow-2xl w-72 rounded-2xl">
                
                {{-- Header del usuario --}}
                <div class="px-4 py-4 text-white bg-gradient-to-r from-indigo-600 to-cyan-500">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-12 h-12 text-xl font-bold rounded-full shadow-lg bg-white/20 backdrop-blur">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-bold">{{ auth()->user()->name }}</h3>
                            <p class="text-xs text-indigo-100">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>

                {{-- Información del usuario --}}
                <div class="px-4 py-3 border-b bg-slate-50">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase">Rol</p>
                            <p class="mt-1 text-sm font-medium text-slate-700">
                                {{ auth()->user()->roles->first()->name ?? 'Sin rol' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-400 uppercase">Último acceso</p>
                            <p class="mt-1 text-xs font-medium text-slate-700">
                                {{ auth()->user()->last_login_at ? \Carbon\Carbon::parse(auth()->user()->last_login_at)->diffForHumans() : 'Primera vez' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Opción de perfil --}}
                <div class="py-2">
                    <a href="{{ route('usuarios.edit', auth()->user()) }}" 
                       class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-indigo-50 group">
                        <div class="flex items-center justify-center w-8 h-8 transition-colors rounded-lg bg-slate-100 group-hover:bg-indigo-100">
                            <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-700 group-hover:text-indigo-600">Mi Perfil</p>
                            <p class="text-xs text-gray-400">Ver y editar tu información personal</p>
                        </div>
                        <svg class="w-4 h-4 transition-opacity opacity-0 text-slate-400 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                {{-- Cerrar sesión --}}
                <div class="px-4 py-3 border-t bg-slate-50">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center w-full gap-3 px-3 py-2 transition-colors rounded-xl bg-red-50 hover:bg-red-100 group">
                            <div class="flex items-center justify-center w-8 h-8 transition-colors bg-red-100 rounded-lg group-hover:bg-red-200">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <div class="flex-1 text-left">
                                <p class="text-sm font-medium text-red-700">Cerrar Sesión</p>
                                <p class="text-xs text-red-400">Salir de la aplicación</p>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>

<style>
[x-cloak] { display: none !important; }

/* Animaciones suaves */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Scrollbar personalizada para dropdowns */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>