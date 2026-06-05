{{-- resources/views/partials/sidebar.blade.php --}}
@php
$empresaActivaId = session('empresa_activa_id', auth()->user()->empresa_id);
$empresaActiva = \App\Models\Empresa::with('licencia')->find($empresaActivaId);

$sucursalActivaId = session('sucursal_activa_id', auth()->user()->sucursal_id);
$sucursalActiva = $sucursalActivaId ? \App\Models\Sucursal::find($sucursalActivaId) : null;

// Optimizar consulta - solo obtener lo necesario
$modulos = \App\Models\Modulo::with(['menus' => function($query) {
    $query->where('activo', true)
        ->whereNull('menu_padre_id')
        ->with(['hijos' => function($q) {
            $q->where('activo', true)->orderBy('orden');
        }])
        ->orderBy('orden');
}])
->where('activo', true)
->orderBy('orden')
->get();

// Pre-calculate active routes para evitar múltiples llamadas
$currentPath = request()->path();
$activeMenus = [];

foreach($modulos as $modulo) {
    foreach($modulo->menus as $menu) {
        if($menu->ruta && request()->is(trim($menu->ruta, '/').'*')) {
            $activeMenus[$menu->id] = true;
        }
        foreach($menu->hijos as $hijo) {
            if(request()->is(trim($hijo->ruta, '/').'*')) {
                $activeMenus[$hijo->id] = true;
                $activeMenus[$menu->id] = true;
            }
        }
    }
}
@endphp

<aside x-data="sidebar()" 
       :class="collapsed ? 'w-20' : 'w-72'"
       class="sticky top-0 z-30 flex flex-col flex-shrink-0 h-screen text-white transition-all duration-300 ease-in-out shadow-xl bg-gradient-to-b from-indigo-700 via-blue-700 to-cyan-600">
    
    {{-- Botón colapsar --}}
    <button @click="toggleCollapse()" 
            class="absolute -right-3 top-20 bg-white rounded-full p-1.5 shadow-lg hover:shadow-xl transition-all duration-200 z-20 group">
        <div class="p-1 transition-colors bg-indigo-600 rounded-full group-hover:bg-indigo-500">
            <svg :class="collapsed ? 'rotate-180' : ''" 
                 class="w-4 h-4 text-white transition-transform duration-300" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </div>
    </button>

    {{-- Header de la empresa - Colapsable --}}
    <div class="p-5 transition-all duration-300 border-b border-white/20 bg-gradient-to-r from-indigo-800/50 to-transparent">
        <div class="flex items-center gap-3" :class="collapsed ? 'justify-center' : ''">
            <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 overflow-hidden bg-white/20 rounded-xl backdrop-blur-sm">
                @if($empresaActiva && $empresaActiva->logo_url)
                    <img src="{{ $empresaActiva->logo_url }}" alt="Logo" class="object-cover w-full h-full">
                @else
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                @endif
            </div>
            <div x-show="!collapsed" class="flex-1 min-w-0">
                <h1 class="text-lg font-bold truncate">{{ $empresaActiva->nombre ?? 'Punto de Venta' }}</h1>
                <p class="text-xs text-blue-100 truncate">{{ $empresaActiva->licencia->nombre ?? 'Sin licencia' }}</p>
                @if($sucursalActiva)
                    <p class="text-[11px] text-blue-200 truncate mt-0.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        </svg>
                        {{ $sucursalActiva->nombre }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Navegación con scroll --}}
    <nav class="flex-1 p-3 space-y-2 overflow-x-hidden overflow-y-auto scrollbar-thin scrollbar-thumb-white/20 scrollbar-track-transparent hover:scrollbar-thumb-white/30">
        @foreach($modulos as $modulo)
            @can('ver_' . strtolower($modulo->nombre))
                <div class="mb-3">
                    {{-- Encabezado del módulo --}}
                    <div class="mb-2 px-3 py-1.5 text-[11px] tracking-wider text-blue-200 uppercase flex items-center gap-2 font-semibold"
                         :class="collapsed ? 'justify-center' : ''">
                        @if($modulo->icono)
                            <span class="flex-shrink-0 text-base">{!! $modulo->icono !!}</span>
                        @else
                            <svg class="flex-shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        @endif
                        <span x-show="!collapsed" class="whitespace-nowrap">{{ $modulo->nombre }}</span>
                    </div>

                    {{-- Menús del módulo --}}
                    <div class="space-y-1">
                        @foreach($modulo->menus as $menu)
                            @php
                                $isMenuActive = isset($activeMenus[$menu->id]);
                                $hasChildren = $menu->hijos->count() > 0;
                            @endphp
                            
                            @if($hasChildren)
                                <div x-data="{ openSubmenu: {{ $isMenuActive ? 'true' : 'false' }} }">
                                    <div @click="openSubmenu = !openSubmenu"
                                         class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 cursor-pointer"
                                         :class="{
                                             'justify-center': collapsed,
                                             'justify-between': !collapsed,
                                             'bg-white/20 font-semibold shadow-sm': {{ $isMenuActive ? 'true' : 'false' }}
                                         }">
                                        <div class="flex items-center gap-2" :class="collapsed ? '' : 'flex-1'">
                                            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 transition-all duration-200"
                                                  :class="{
                                                      'bg-white': {{ $isMenuActive ? 'true' : 'false' }},
                                                      'bg-blue-200': {{ !$isMenuActive ? 'true' : 'false' }}
                                                  }"></span>
                                            <span x-show="!collapsed" class="whitespace-nowrap">{{ $menu->nombre }}</span>
                                        </div>
                                        <svg x-show="!collapsed" 
                                             class="flex-shrink-0 w-4 h-4 transition-transform duration-200" 
                                             :class="{'rotate-90': openSubmenu}" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                    
                                    <div x-show="openSubmenu" 
                                         x-collapse.duration.300ms 
                                         class="pl-2 mt-1 ml-4 space-y-1 border-l-2 border-white/20">
                                        @foreach($menu->hijos as $hijo)
                                            @php $isHijoActive = isset($activeMenus[$hijo->id]); @endphp
                                            <a href="{{ url($hijo->ruta) }}"
                                               class="flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-[13px] group"
                                               :class="{
                                                   'bg-white/20 font-medium': {{ $isHijoActive ? 'true' : 'false' }},
                                                   'hover:bg-white/10 hover:pl-4': true
                                               }">
                                                <span class="w-1 h-1 transition-colors duration-200 rounded-full"
                                                      :class="{
                                                          'bg-white': {{ $isHijoActive ? 'true' : 'false' }},
                                                          'bg-blue-300 group-hover:bg-white': {{ !$isHijoActive ? 'true' : 'false' }}
                                                      }"></span>
                                                <span>{{ $hijo->nombre }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ url($menu->ruta) }}"
                                   class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 group"
                                   :class="{
                                       'justify-center': collapsed,
                                       'bg-white/20 font-semibold shadow-sm': {{ $isMenuActive ? 'true' : 'false' }}
                                   }">
                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0 transition-all duration-200"
                                          :class="{
                                              'bg-white': {{ $isMenuActive ? 'true' : 'false' }},
                                              'bg-blue-200': {{ !$isMenuActive ? 'true' : 'false' }}
                                          }"></span>
                                    <span x-show="!collapsed" class="whitespace-nowrap">{{ $menu->nombre }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endcan
        @endforeach
    </nav>

    {{-- Footer - Colapsable --}}
    <div class="p-4 mt-auto transition-all duration-300 border-t border-white/20 bg-gradient-to-r from-transparent to-indigo-800/20">
        <div x-show="!collapsed" class="space-y-2 text-xs">
            <div class="flex items-center justify-between">
                <span class="text-blue-200">Licencia:</span>
                <span class="font-medium {{ $empresaActiva->fecha_fin && $empresaActiva->fecha_fin->isPast() ? 'text-red-300' : 'text-white' }}">
                    {{ $empresaActiva->fecha_fin?->format('d/m/Y') ?? 'N/A' }}
                </span>
            </div>
            <div class="flex items-center gap-2 text-blue-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span>v2.0</span>
            </div>
        </div>
        <div x-show="collapsed" class="flex justify-center">
            <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
    </div>
</aside>

<script>
function sidebar() {
    return {
        collapsed: localStorage.getItem('sidebarCollapsed') === 'true',
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebarCollapsed', this.collapsed);
        }
    }
}
</script>