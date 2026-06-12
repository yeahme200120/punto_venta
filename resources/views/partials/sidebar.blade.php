@php
    $user = auth()->user();
    $empresaActivaId = session('empresa_activa_id', auth()->user()->empresa_id);
    $empresaActiva = \App\Models\Empresa::with('licencia')->find($empresaActivaId);
    $sucursalActivaId = session('sucursal_activa_id', auth()->user()->sucursal_id);
    $sucursalActiva = $sucursalActivaId ? \App\Models\Sucursal::find($sucursalActivaId) : null;

    function puedeVerMenu($menu, $user) {
        if ($user->hasRole('Super Admin')) return true;
        if (!empty($menu->permiso)) {
            if (!$user->can($menu->permiso)) return false;
        }
        if ($menu->hijos->count() > 0) {
            $tieneHijoVisible = false;
            foreach ($menu->hijos as $hijo) {
                if (empty($hijo->permiso) || $user->can($hijo->permiso)) {
                    $tieneHijoVisible = true;
                    break;
                }
            }
            return $tieneHijoVisible;
        }
        return true;
    }

    $modulos = \App\Models\Modulo::with([
        'menus' => function ($query) {
            $query->where('activo', true)
                ->whereNull('menu_padre_id')
                ->with(['hijos' => function ($q) {
                    $q->where('activo', true)->orderBy('orden');
                }])
                ->orderBy('orden');
        }
    ])->where('activo', true)->orderBy('orden')->get();

    $currentPath = request()->path();
    
    // Construir array de IDs de menús activos
    $activeMenuIds = [];
    $activeParentIds = []; // Menús padre que deben estar abiertos
    
    foreach ($modulos as $modulo) {
        foreach ($modulo->menus as $menu) {
            if ($menu->ruta && request()->is(trim($menu->ruta, '/') . '*')) {
                $activeMenuIds[] = $menu->id;
            }
            foreach ($menu->hijos as $hijo) {
                if (request()->is(trim($hijo->ruta, '/') . '*')) {
                    $activeMenuIds[] = $hijo->id;
                    $activeMenuIds[] = $menu->id;
                    $activeParentIds[] = $menu->id;
                }
            }
        }
    }
    $activeMenuIds = array_unique($activeMenuIds);
    $activeParentIds = array_unique($activeParentIds);
@endphp

<aside id="sidebar" 
       class="sticky top-0 z-30 flex flex-col flex-shrink-0 w-64 h-screen text-white transition-all duration-300 ease-in-out shadow-xl bg-gradient-to-b from-indigo-700 via-blue-700 to-cyan-600">

    {{-- Botón colapsar --}}
    <button id="collapseSidebarBtn" 
            class="absolute -right-3 top-20 bg-white rounded-full p-1.5 shadow-lg hover:shadow-xl transition-all duration-200 z-20 group">
        <div class="p-1 transition-colors bg-indigo-600 rounded-full group-hover:bg-indigo-500">
            <svg id="collapseIcon" 
                 class="w-4 h-4 text-white transition-transform duration-300" 
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </div>
    </button>

    {{-- Header de la empresa --}}
    <div id="sidebarHeader" class="p-4 transition-all duration-300 border-b border-white/20 bg-gradient-to-r from-indigo-800/50 to-transparent">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 overflow-hidden bg-white/20 rounded-xl backdrop-blur-sm">
                @if($empresaActiva && $empresaActiva->logo_url)
                    <img src="{{ $empresaActiva->logo_url }}" alt="Logo" class="object-cover w-full h-full">
                @else
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                @endif
            </div>
            <div id="sidebarHeaderText" class="flex-1 min-w-0">
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

    {{-- Navegación --}}
    <nav class="flex-1 p-3 space-y-2 overflow-x-hidden overflow-y-auto scrollbar-thin">
        @foreach($modulos as $moduloIndex => $modulo)
            @php
                $menusVisibles = $modulo->menus->filter(function($menu) use ($user) {
                    return puedeVerMenu($menu, $user);
                });
            @endphp
            
            @if($menusVisibles->count() > 0)
                <div class="mb-3">
                    <div id="moduloHeader{{ $moduloIndex }}" class="mb-2 px-3 py-1.5 text-[11px] tracking-wider text-blue-200 uppercase flex items-center gap-2 font-semibold">
                        @if($modulo->icono)
                            <span class="flex-shrink-0 text-base">{!! $modulo->icono !!}</span>
                        @else
                            <svg class="flex-shrink-0 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        @endif
                        <span class="sidebar-text whitespace-nowrap">{{ $modulo->nombre }}</span>
                    </div>

                    <div class="space-y-1">
                        @foreach($menusVisibles as $menuIndex => $menu)
                            @php
                                $hasChildren = $menu->hijos->count() > 0;
                                $hijosVisibles = $hasChildren ? $menu->hijos->filter(function($hijo) use ($user) {
                                    return !$hijo->permiso || $user->can($hijo->permiso);
                                }) : collect();
                                $menuKey = "submenu_{$moduloIndex}_{$menuIndex}";
                                $isMenuActive = in_array($menu->id, $activeMenuIds);
                                $isParentActive = in_array($menu->id, $activeParentIds);
                            @endphp

                            @if($hasChildren && $hijosVisibles->count() > 0)
                                {{-- Menú con submenús --}}
                                <div class="relative">
                                    <div data-submenu-toggle="{{ $menuKey }}"
                                         class="submenu-toggle flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 cursor-pointer hover:bg-white/10 {{ $isMenuActive ? 'bg-white text-slate-900 shadow-lg font-semibold' : '' }}">
                                        <div class="flex items-center flex-1 gap-2">
                                            <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full {{ $isMenuActive ? 'bg-slate-600' : 'bg-blue-200' }}"></span>
                                            <span class="sidebar-text whitespace-nowrap">{{ $menu->nombre }}</span>
                                        </div>
                                        <svg class="submenu-arrow flex-shrink-0 w-4 h-4 transition-transform duration-200 {{ $isParentActive ? 'rotate-90' : '' }}" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>

                                    <div data-submenu-content="{{ $menuKey }}"
                                         class="submenu-content pl-2 mt-1 ml-4 space-y-1 border-l-2 border-white/20 {{ $isParentActive ? '' : 'hidden' }}">
                                        @foreach($hijosVisibles as $hijo)
                                            @php $isHijoActive = in_array($hijo->id, $activeMenuIds); @endphp
                                            <a href="{{ url($hijo->ruta) }}" 
                                               class="flex items-center gap-2 px-3 py-2 rounded-lg transition-all duration-200 text-[13px] hover:bg-white/10 hover:pl-4 {{ $isHijoActive ? 'bg-white/20 font-medium' : '' }}">
                                                <span class="w-1 h-1 rounded-full {{ $isHijoActive ? 'bg-white' : 'bg-blue-300' }}"></span>
                                                <span>{{ $hijo->nombre }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif(!$hasChildren)
                                {{-- Menú sin submenús --}}
                                <a href="{{ url($menu->ruta) }}"
                                   class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm transition-all duration-200 hover:bg-white/10 {{ $isMenuActive ? 'bg-white/20 font-semibold shadow-sm' : '' }}">
                                    <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full {{ $isMenuActive ? 'bg-white' : 'bg-blue-200' }}"></span>
                                    <span class="sidebar-text whitespace-nowrap">{{ $menu->nombre }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    {{-- Footer --}}
    <div id="sidebarFooter" class="p-4 mt-auto transition-all duration-300 border-t border-white/20 bg-gradient-to-r from-transparent to-indigo-800/20">
        <div id="sidebarFooterExpanded" class="space-y-2 text-xs">
            <div class="flex items-center justify-between">
                <span class="text-blue-200">Licencia:</span>
                <span class="font-medium {{ $empresaActiva && $empresaActiva->fecha_fin && $empresaActiva->fecha_fin->isPast() ? 'text-red-300' : 'text-white' }}">
                    {{ $empresaActiva && $empresaActiva->fecha_fin ? $empresaActiva->fecha_fin->format('d/m/Y') : 'N/A' }}
                </span>
            </div>
            <div class="flex items-center gap-2 text-blue-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span>v2.0</span>
            </div>
        </div>
        <div id="sidebarFooterCollapsed" class="justify-center hidden">
            <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
        </div>
    </div>
</aside>

<script>
    // ============================================================
    // 🪵 LOGS DE DIAGNÓSTICO - DOBLE CLIC SIDEBAR (CORREGIDO)
    // ============================================================
    
    // ✅ EVITAR DOBLE EJECUCIÓN
    if (window.__sidebarInitialized) {
        console.warn('⚠️ [SIDEBAR] Ya estaba inicializado. SALTANDO segunda ejecución.');
        // No hacer nada más
    } else {
        window.__sidebarInitialized = true;
        
        // ✅ ESPERAR A QUE EL DOM ESTÉ COMPLETO
        document.addEventListener('DOMContentLoaded', function() {
            
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('🟢 [SIDEBAR] DOMContentLoaded - INICIALIZANDO');
            console.log('🟢 [SIDEBAR] Timestamp:', new Date().toISOString());
            console.log('🟢 [SIDEBAR] URL actual:', window.location.href);
            console.log('🟢 [SIDEBAR] Alpine.js:', typeof Alpine !== 'undefined' ? 'PRESENTE ✅' : 'NO');
            console.log('🟢 [SIDEBAR] DOM Ready:', document.readyState);
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            
            // Elementos del DOM
            const sidebar = document.getElementById('sidebar');
            const collapseBtn = document.getElementById('collapseSidebarBtn');
            const collapseIcon = document.getElementById('collapseIcon');
            const sidebarTexts = document.querySelectorAll('.sidebar-text');
            const sidebarHeader = document.getElementById('sidebarHeader');
            const sidebarHeaderText = document.getElementById('sidebarHeaderText');
            const sidebarFooterExpanded = document.getElementById('sidebarFooterExpanded');
            const sidebarFooterCollapsed = document.getElementById('sidebarFooterCollapsed');
            const moduloHeaders = document.querySelectorAll('[id^="moduloHeader"]');
            const submenuToggles = document.querySelectorAll('[data-submenu-toggle]');
            
            console.log('📋 [SIDEBAR] Elementos encontrados:');
            console.log('   sidebar:', sidebar ? '✅' : '❌');
            console.log('   collapseBtn:', collapseBtn ? '✅' : '❌');
            console.log('   submenuToggles:', submenuToggles.length);
            submenuToggles.forEach((t, i) => {
                console.log(`      [${i}] ${t.getAttribute('data-submenu-toggle')} | Visible: ${!t.closest('.hidden')}`);
            });
            
            let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            console.log('🔑 [SIDEBAR] localStorage sidebarCollapsed:', isCollapsed);
            
            // ============================================================
            // COLAPSAR SIDEBAR
            // ============================================================
            
            function applyCollapseState() {
                console.log('🔄 [SIDEBAR] applyCollapseState() - isCollapsed:', isCollapsed);
                
                if (isCollapsed) {
                    sidebar?.classList.remove('w-64');
                    sidebar?.classList.add('w-16');
                    collapseIcon?.classList.add('rotate-180');
                    sidebarTexts.forEach(el => el.style.display = 'none');
                    sidebarHeader?.classList.add('justify-center');
                    if (sidebarHeaderText) sidebarHeaderText.style.display = 'none';
                    if (sidebarFooterExpanded) sidebarFooterExpanded.style.display = 'none';
                    if (sidebarFooterCollapsed) sidebarFooterCollapsed.style.display = 'flex';
                    moduloHeaders.forEach(el => {
                        el.classList.add('justify-center');
                        const text = el.querySelector('.sidebar-text');
                        if (text) text.style.display = 'none';
                    });
                } else {
                    sidebar?.classList.remove('w-16');
                    sidebar?.classList.add('w-64');
                    collapseIcon?.classList.remove('rotate-180');
                    sidebarTexts.forEach(el => el.style.display = '');
                    sidebarHeader?.classList.remove('justify-center');
                    if (sidebarHeaderText) sidebarHeaderText.style.display = '';
                    if (sidebarFooterExpanded) sidebarFooterExpanded.style.display = '';
                    if (sidebarFooterCollapsed) sidebarFooterCollapsed.style.display = 'none';
                    moduloHeaders.forEach(el => {
                        el.classList.remove('justify-center');
                        const text = el.querySelector('.sidebar-text');
                        if (text) text.style.display = '';
                    });
                }
            }
            
            if (collapseBtn) {
                // ✅ Reemplazar para eliminar listeners antiguos
                const newCollapseBtn = collapseBtn.cloneNode(true);
                collapseBtn.parentNode.replaceChild(newCollapseBtn, collapseBtn);
                
                newCollapseBtn.addEventListener('click', function(e) {
                    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                    console.log('🔘 [SIDEBAR] CLICK en botón COLAPSAR');
                    console.log('   Estado actual → Nuevo estado:', isCollapsed, '→', !isCollapsed);
                    
                    e.preventDefault();
                    e.stopPropagation();
                    
                    isCollapsed = !isCollapsed;
                    localStorage.setItem('sidebarCollapsed', isCollapsed);
                    applyCollapseState();
                    console.log('   ✅ Colapso aplicado');
                    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                });
                
                console.log('✅ [SIDEBAR] Listener COLAPSAR registrado');
            }
            
            // ============================================================
            // SUBMENÚS - REEMPLAZAR para evitar listeners duplicados
            // ============================================================
            
            console.log('📂 [SIDEBAR] Registrando SUBMENÚS...');
            
            const freshToggles = document.querySelectorAll('[data-submenu-toggle]');
            
            freshToggles.forEach((toggle, index) => {
                const menuKey = toggle.getAttribute('data-submenu-toggle');
                const content = document.querySelector(`[data-submenu-content="${menuKey}"]`);
                const arrow = toggle.querySelector('.submenu-arrow');
                
                console.log(`   [${index}] ${menuKey} | Estado: ${content?.classList.contains('hidden') ? 'OCULTO' : 'VISIBLE'}`);
                
                // ✅ REEMPLAZAR elemento para eliminar listeners viejos
                const newToggle = toggle.cloneNode(true);
                toggle.parentNode.replaceChild(newToggle, toggle);
                
                let clickCounter = 0;
                
                newToggle.addEventListener('click', function(e) {
                    clickCounter++;
                    
                    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                    console.log(`📂 [SIDEBAR] CLIC #${clickCounter} en: ${menuKey}`);
                    
                    if (clickCounter > 1) {
                        console.warn('   ⚠️ ¡DOBLE CLIC! Contador:', clickCounter);
                    }
                    
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // ✅ Re-obtener contenido (puede haber cambiado)
                    const currentContent = document.querySelector(`[data-submenu-content="${menuKey}"]`);
                    const currentArrow = newToggle.querySelector('.submenu-arrow');
                    
                    if (currentContent) {
                        const estabaOculto = currentContent.classList.contains('hidden');
                        currentContent.classList.toggle('hidden');
                        const ahoraOculto = currentContent.classList.contains('hidden');
                        
                        console.log(`   ${estabaOculto ? 'ABRIENDO' : 'CERRANDO'} → Ahora: ${ahoraOculto ? 'OCULTO' : 'VISIBLE'}`);
                        
                        if (currentArrow) {
                            currentArrow.classList.toggle('rotate-90');
                        }
                    }
                    
                    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
                });
            });
            
            console.log(`✅ [SIDEBAR] ${freshToggles.length} submenús registrados`);
            
            // ============================================================
            // APLICAR ESTADO
            // ============================================================
            
            applyCollapseState();
            
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            console.log('✅ [SIDEBAR] INICIALIZACIÓN COMPLETA');
            console.log('   Submenús:', freshToggles.length);
            console.log('   URL:', window.location.href);
            console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            
        }); // FIN DOMContentLoaded
    } // FIN verificación window.__sidebarInitialized
</script>

<style>
    .scrollbar-thin::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }
    .scrollbar-thin::-webkit-scrollbar-track {
        background: transparent;
        border-radius: 10px;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    .scrollbar-thin {
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
    }
</style>