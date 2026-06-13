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
    <nav class="flex-1 p-3 space-y-2 overflow-y-auto" style="scrollbar-width: thin">
        @foreach($modulos as $moduloIndex => $modulo)
            @php
                $menusVisibles = $modulo->menus->filter(function($menu) use ($user) {
                    return \App\ViewComposers\SidebarComposer::puedeVerMenu($menu, $user);
                });
            @endphp
            
            @if($menusVisibles->count() > 0)
                <div class="mb-3">
                    <div class="mb-2 px-3 py-1 text-[10px] tracking-wider text-blue-200 uppercase flex items-center gap-2 font-semibold">
                        @if($modulo->icono)
                            <span class="flex-shrink-0 text-sm">{!! $modulo->icono !!}</span>
                        @endif
                        <span class="sidebar-text whitespace-nowrap">{{ $modulo->nombre }}</span>
                    </div>

                    <div class="space-y-0.5">
                        @foreach($menusVisibles as $menuIndex => $menu)
                            @php
                                $hasChildren = $menu->hijos->count() > 0;
                                $hijosVisibles = $hasChildren ? $menu->hijos->filter(function($hijo) use ($user) {
                                    return !$hijo->permiso || $user->can($hijo->permiso);
                                }) : collect();
                                $menuKey = "submenu_{$moduloIndex}_{$menuIndex}";
                                $isParentActive = in_array($menu->id, $activeParentIds);
                                $isMenuActive = in_array($menu->id, $activeMenuIds);
                            @endphp

                            @if($hasChildren && $hijosVisibles->count() > 0)
                                <div>
                                    <div data-submenu-toggle="{{ $menuKey }}"
                                         class="flex items-center justify-between px-3 py-2 rounded-lg text-sm cursor-pointer transition-all duration-200 hover:bg-white/10 {{ $isParentActive ? 'bg-white/10' : '' }}">
                                        <div class="flex items-center gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $isMenuActive ? 'bg-white' : 'bg-blue-300' }}"></span>
                                            <span class="sidebar-text whitespace-nowrap">{{ $menu->nombre }}</span>
                                        </div>
                                        <svg class="submenu-arrow w-3 h-3 transition-transform duration-200 text-blue-300 {{ $isParentActive ? 'rotate-90' : '' }}" 
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>

                                    <div data-submenu-content="{{ $menuKey }}"
                                         class="submenu-content pl-4 ml-2 mt-1 space-y-0.5 border-l border-white/20 {{ $isParentActive ? '' : 'hidden' }}">
                                        @foreach($hijosVisibles as $hijo)
                                            @php $isHijoActive = in_array($hijo->id, $activeMenuIds); @endphp
                                            <a href="{{ url($hijo->ruta) }}" 
                                               class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs transition-all duration-200 hover:bg-white/10 hover:pl-4 {{ $isHijoActive ? 'bg-white/20 font-medium' : '' }}">
                                                <span class="w-1 h-1 rounded-full {{ $isHijoActive ? 'bg-white' : 'bg-blue-400' }}"></span>
                                                <span>{{ $hijo->nombre }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif(!$hasChildren)
                                <a href="{{ url($menu->ruta) }}"
                                   class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-all duration-200 hover:bg-white/10 {{ $isMenuActive ? 'bg-white/20 font-semibold' : '' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $isMenuActive ? 'bg-white' : 'bg-blue-300' }}"></span>
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

<style>
    nav::-webkit-scrollbar {
        width: 4px;
    }
    nav::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }
    nav::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
    }
    .submenu-content {
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }
    .submenu-content:not(.hidden) {
        max-height: 500px;
    }
    .submenu-content.hidden {
        max-height: 0;
    }
</style>

<script>
if (!window._sidebarJsExecuted) {
    window._sidebarJsExecuted = true;
    
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const collapseBtn = document.getElementById('collapseSidebarBtn');
        const collapseIcon = document.getElementById('collapseIcon');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const sidebarHeader = document.getElementById('sidebarHeader');
        const sidebarHeaderText = document.getElementById('sidebarHeaderText');
        const sidebarFooterExpanded = document.getElementById('sidebarFooterExpanded');
        const sidebarFooterCollapsed = document.getElementById('sidebarFooterCollapsed');
        
        let isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        
        function applyCollapseState() {
            if (!sidebar) return;
            if (isCollapsed) {
                sidebar.style.width = '68px';
                if (collapseIcon) collapseIcon.classList.add('rotate-180');
                sidebarTexts.forEach(el => el.style.display = 'none');
                if (sidebarHeader) sidebarHeader.classList.add('justify-center');
                if (sidebarHeaderText) sidebarHeaderText.style.display = 'none';
                if (sidebarFooterExpanded) sidebarFooterExpanded.style.display = 'none';
                if (sidebarFooterCollapsed) sidebarFooterCollapsed.style.display = 'flex';
            } else {
                sidebar.style.width = '260px';
                if (collapseIcon) collapseIcon.classList.remove('rotate-180');
                sidebarTexts.forEach(el => el.style.display = '');
                if (sidebarHeader) sidebarHeader.classList.remove('justify-center');
                if (sidebarHeaderText) sidebarHeaderText.style.display = '';
                if (sidebarFooterExpanded) sidebarFooterExpanded.style.display = '';
                if (sidebarFooterCollapsed) sidebarFooterCollapsed.style.display = 'none';
            }
        }
        
        if (collapseBtn) {
            const newBtn = collapseBtn.cloneNode(true);
            collapseBtn.parentNode.replaceChild(newBtn, collapseBtn);
            newBtn.addEventListener('click', function(e) {
                e.preventDefault();
                isCollapsed = !isCollapsed;
                localStorage.setItem('sidebarCollapsed', isCollapsed);
                applyCollapseState();
            });
        }
        
        const submenuToggles = document.querySelectorAll('[data-submenu-toggle]');
        const submenuContents = document.querySelectorAll('[data-submenu-content]');
        
        function closeAllSubmenus() {
            submenuContents.forEach(content => {
                content.classList.add('hidden');
                const toggle = document.querySelector(`[data-submenu-toggle="${content.getAttribute('data-submenu-content')}"]`);
                if (toggle) {
                    const arrow = toggle.querySelector('.submenu-arrow');
                    if (arrow) arrow.classList.remove('rotate-90');
                }
            });
        }
        
        submenuToggles.forEach(toggle => {
            const menuKey = toggle.getAttribute('data-submenu-toggle');
            const content = document.querySelector(`[data-submenu-content="${menuKey}"]`);
            if (!content) return;
            
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);
            
            newToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isOpen = !content.classList.contains('hidden');
                closeAllSubmenus();
                if (!isOpen) {
                    content.classList.remove('hidden');
                    const arrow = newToggle.querySelector('.submenu-arrow');
                    if (arrow) arrow.classList.add('rotate-90');
                }
            });
        });
        
        applyCollapseState();
    });
}
</script>