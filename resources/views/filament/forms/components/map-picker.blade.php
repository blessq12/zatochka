@php
    $latField = $getLatitudeField() ?? 'latitude';
    $lngField = $getLongitudeField() ?? 'longitude';
    $livewire = $getLivewire();
    $lat = data_get($livewire, "data.{$latField}", data_get($livewire, "{$latField}", 55.7558));
    $lng = data_get($livewire, "data.{$lngField}", data_get($livewire, "{$lngField}", 37.6173));
    $uniqueId = 'map-' . str_replace(['.', '-'], '', uniqid('', true));
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="space-y-4">
        <div class="flex gap-2">
            <input
                type="text"
                id="{{ $uniqueId }}-address"
                placeholder="Введите адрес для поиска на карте..."
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm px-3 py-2"
            >
            <button
                type="button"
                id="{{ $uniqueId }}-search-btn"
                class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Найти
            </button>
        </div>
        
        <div 
            id="{{ $uniqueId }}"
            style="height: 400px; width: 100%; z-index: 0; background: #f3f4f6;"
            class="rounded-lg border border-gray-300"
        >
            <div class="flex items-center justify-center h-full text-gray-500" id="{{ $uniqueId }}-loading">
                Загрузка карты...
            </div>
        </div>
        
        <div class="flex gap-4 text-sm" id="{{ $uniqueId }}-coords" style="display: none;">
            <div class="flex-1">
                <span class="text-gray-600 font-medium">Широта:</span>
                <span class="ml-2 text-gray-900" id="{{ $uniqueId }}-lat">{{ is_numeric($lat) ? number_format($lat, 6) : 'не указана' }}</span>
            </div>
            <div class="flex-1">
                <span class="text-gray-600 font-medium">Долгота:</span>
                <span class="ml-2 text-gray-900" id="{{ $uniqueId }}-lng">{{ is_numeric($lng) ? number_format($lng, 6) : 'не указана' }}</span>
            </div>
        </div>
        
        <p class="text-xs text-gray-500">
            Кликните на карте или перетащите маркер для выбора местоположения. Координаты обновятся автоматически.
        </p>
    </div>
</x-dynamic-component>

<script>
(function() {
    'use strict';
    
    const mapId = '{{ $uniqueId }}';
    const latField = '{{ $latField }}';
    const lngField = '{{ $lngField }}';
    const initialLat = {{ is_numeric($lat) ? $lat : 55.7558 }};
    const initialLng = {{ is_numeric($lng) ? $lng : 37.6173 }};
    
    let map = null;
    let marker = null;
    let currentLat = initialLat;
    let currentLng = initialLng;
    
    function loadLeaflet() {
        return new Promise((resolve, reject) => {
            if (typeof L !== 'undefined') {
                resolve();
                return;
            }
            
            // Load CSS
            if (!document.querySelector('link[href*="leaflet.css"]')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(link);
            }
            
            // Load JS
            const existingScript = document.querySelector('script[src*="leaflet.js"]');
            if (existingScript && window.L) {
                resolve();
                return;
            }
            
            if (!existingScript) {
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = () => resolve();
                script.onerror = () => reject(new Error('Failed to load Leaflet'));
                document.head.appendChild(script);
            } else {
                // Script is loading, wait for it
                const checkInterval = setInterval(() => {
                    if (window.L) {
                        clearInterval(checkInterval);
                        resolve();
                    }
                }, 100);
                
                setTimeout(() => {
                    clearInterval(checkInterval);
                    if (!window.L) {
                        reject(new Error('Leaflet timeout'));
                    }
                }, 10000);
            }
        });
    }
    
    function initMap() {
        const container = document.getElementById(mapId);
        if (!container) {
            console.error('Map container not found:', mapId);
            return;
        }
        
        const loadingEl = document.getElementById(mapId + '-loading');
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
        
        const coordsEl = document.getElementById(mapId + '-coords');
        if (coordsEl) {
            coordsEl.style.display = 'flex';
        }
        
        try {
            map = L.map(mapId).setView([currentLat, currentLng], 13);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(map);
            
            marker = L.marker([currentLat, currentLng], { draggable: true }).addTo(map);
            
            map.on('click', function(e) {
                updatePosition(e.latlng.lat, e.latlng.lng);
            });
            
            marker.on('dragend', function(e) {
                const pos = e.target.getLatLng();
                updatePosition(pos.lat, pos.lng);
            });
        } catch (error) {
            console.error('Error initializing map:', error);
            if (loadingEl) {
                loadingEl.textContent = 'Ошибка загрузки карты';
            }
        }
    }
    
    function updatePosition(lat, lng) {
        currentLat = parseFloat(parseFloat(lat).toFixed(6));
        currentLng = parseFloat(parseFloat(lng).toFixed(6));
        
        // Update marker
        if (marker) {
            marker.setLatLng([currentLat, currentLng]);
            if (map) {
                map.setView([currentLat, currentLng], map.getZoom());
            }
        }
        
        // Update display
        const latDisplay = document.getElementById(mapId + '-lat');
        const lngDisplay = document.getElementById(mapId + '-lng');
        if (latDisplay) latDisplay.textContent = currentLat.toFixed(6);
        if (lngDisplay) lngDisplay.textContent = currentLng.toFixed(6);
        
        // Update form fields via Livewire
        try {
            if (typeof window.Livewire !== 'undefined') {
                // Try to find Livewire component by checking all components
                const allComponents = window.Livewire.all();
                if (allComponents && allComponents.length > 0) {
                    const component = allComponents[0];
                    if (component && component.set) {
                        component.set('data.' + latField, currentLat);
                        component.set('data.' + lngField, currentLng);
                    }
                }
            }
        } catch (e) {
            console.warn('Livewire update failed:', e);
        }
        
        // Fallback: Update inputs directly
        setTimeout(() => {
            const latInputs = document.querySelectorAll('input[name*="[' + latField + ']"], input[name="data[' + latField + ']"]');
            const lngInputs = document.querySelectorAll('input[name*="[' + lngField + ']"], input[name="data[' + lngField + ']"]');
            
            latInputs.forEach(input => {
                input.value = currentLat;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
            
            lngInputs.forEach(input => {
                input.value = currentLng;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }, 50);
    }
    
    function geocodeAddress() {
        console.log('geocodeAddress called');
        const addressInput = document.getElementById(mapId + '-address');
        const searchBtn = document.getElementById(mapId + '-search-btn');
        const address = addressInput ? addressInput.value.trim() : '';
        
        console.log('Address:', address);
        
        if (!address) {
            alert('Введите адрес для поиска');
            return;
        }
        
        if (searchBtn) {
            searchBtn.disabled = true;
            searchBtn.textContent = 'Поиск...';
        }
        
        const url = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(address) + '&limit=1&accept-language=ru&addressdetails=1';
        
        console.log('Fetching:', url);
        
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Geocoding response:', data);
                if (data && data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lon = parseFloat(result.lon);
                    
                    console.log('Found coordinates:', lat, lon);
                    
                    if (isNaN(lat) || isNaN(lon)) {
                        throw new Error('Invalid coordinates in response');
                    }
                    
                    updatePosition(lat, lon);
                    if (addressInput && result.display_name) {
                        addressInput.value = result.display_name;
                    }
                } else {
                    alert('Адрес не найден. Попробуйте ввести более конкретный адрес.');
                }
            })
            .catch(error => {
                console.error('Geocoding error:', error);
                alert('Ошибка при поиске адреса: ' + error.message);
            })
            .finally(() => {
                if (searchBtn) {
                    searchBtn.disabled = false;
                    searchBtn.textContent = 'Найти';
                }
            });
    }
    
    // Setup event listeners - use event delegation to avoid issues with Livewire
    function setupSearchListeners() {
        const searchBtn = document.getElementById(mapId + '-search-btn');
        const addressInput = document.getElementById(mapId + '-address');
        
        if (searchBtn) {
            // Check if listener already attached
            if (!searchBtn.dataset.listenerAttached) {
                searchBtn.dataset.listenerAttached = 'true';
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Search button clicked for:', mapId);
                    geocodeAddress();
                });
                console.log('Search button listener attached for:', mapId);
            }
        }
        
        if (addressInput) {
            // Check if listener already attached
            if (!addressInput.dataset.listenerAttached) {
                addressInput.dataset.listenerAttached = 'true';
                addressInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Enter pressed in address input for:', mapId);
                        geocodeAddress();
                    }
                });
                console.log('Address input listener attached for:', mapId);
            }
        }
    }
    
    // Initialize when DOM is ready
    function initializeComponent() {
        console.log('Initializing map component:', mapId);
        
        // Setup search listeners immediately
        setupSearchListeners();
        
        // Retry if elements not found (for Livewire dynamic updates)
        setTimeout(() => {
            const searchBtn = document.getElementById(mapId + '-search-btn');
            const addressInput = document.getElementById(mapId + '-address');
            if (!searchBtn || !addressInput) {
                console.log('Retrying to setup listeners...');
                setupSearchListeners();
            }
        }, 1000);
        
        // Wait a bit for DOM to be ready, then initialize map
        setTimeout(() => {
            loadLeaflet().then(initMap).catch(err => {
                console.error('Failed to load Leaflet:', err);
                const loadingEl = document.getElementById(mapId + '-loading');
                if (loadingEl) {
                    loadingEl.textContent = 'Ошибка загрузки библиотеки карт';
                }
            });
        }, 500);
    }
    
    // Use MutationObserver to detect when elements are added to DOM
    const observer = new MutationObserver(function(mutations) {
        const searchBtn = document.getElementById(mapId + '-search-btn');
        const addressInput = document.getElementById(mapId + '-address');
        if (searchBtn && addressInput) {
            setupSearchListeners();
            observer.disconnect();
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeComponent);
    } else {
        initializeComponent();
    }
    
    // Also setup listeners after Livewire updates
    if (typeof window.Livewire !== 'undefined') {
        document.addEventListener('livewire:init', function() {
            setTimeout(initializeComponent, 100);
        });
        document.addEventListener('livewire:update', function() {
            setTimeout(setupSearchListeners, 200);
        });
    }
})();
</script>