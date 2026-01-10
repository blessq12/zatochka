@php
    $latField = $getLatitudeField() ?? 'latitude';
    $lngField = $getLongitudeField() ?? 'longitude';
    $livewire = $getLivewire();
    $lat = data_get($livewire, "data.{$latField}", data_get($livewire, "{$latField}", 55.7558));
    $lng = data_get($livewire, "data.{$lngField}", data_get($livewire, "{$lngField}", 37.6173));
    $uniqueId = 'map-' . str_replace('.', '', uniqid('', true));
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div 
        x-data="{ 
            map: null,
            marker: null,
            lat: {{ is_numeric($lat) ? $lat : 55.7558 }},
            lng: {{ is_numeric($lng) ? $lng : 37.6173 }},
            mapId: '{{ $uniqueId }}',
            latField: '{{ $latField }}',
            lngField: '{{ $lngField }}',
            initialized: false,
            init() {
                this.waitForLeaflet().then(() => {
                    this.$nextTick(() => {
                        setTimeout(() => {
                            this.initMap();
                        }, 300);
                    });
                });
            },
            waitForLeaflet() {
                return new Promise((resolve) => {
                    if (typeof L !== 'undefined' && typeof L.map === 'function') {
                        resolve();
                        return;
                    }
                    
                    let attempts = 0;
                    const maxAttempts = 50;
                    const checkInterval = setInterval(() => {
                        attempts++;
                        if (typeof L !== 'undefined' && typeof L.map === 'function') {
                            clearInterval(checkInterval);
                            resolve();
                        } else if (attempts >= maxAttempts) {
                            clearInterval(checkInterval);
                            console.error('Leaflet failed to load after ' + maxAttempts + ' attempts');
                            resolve(); // Resolve anyway to prevent hanging
                        }
                    }, 100);
                });
            },
            initMap() {
                if (this.initialized) return;
                
                if (typeof L === 'undefined' || typeof L.map !== 'function') {
                    console.error('Leaflet is not available');
                    return;
                }
                
                const element = document.getElementById(this.mapId);
                if (!element) {
                    console.error('Map container not found:', this.mapId);
                    return;
                }
                
                try {
                    this.map = L.map(this.mapId).setView([this.lat, this.lng], 13);
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(this.map);
                    
                    if (this.lat && this.lng) {
                        this.marker = L.marker([this.lat, this.lng], { draggable: true }).addTo(this.map);
                    } else {
                        this.marker = L.marker([55.7558, 37.6173], { draggable: true }).addTo(this.map);
                        this.lat = 55.7558;
                        this.lng = 37.6173;
                    }
                    
                    this.map.on('click', (e) => {
                        const { lat, lng } = e.latlng;
                        this.updatePosition(lat, lng);
                    });
                    
                    this.marker.on('dragend', (e) => {
                        const { lat, lng } = e.target.getLatLng();
                        this.updatePosition(lat, lng);
                    });
                    
                    this.initialized = true;
                } catch (error) {
                    console.error('Error initializing map:', error);
                }
            },
            updatePosition(lat, lng) {
                this.lat = parseFloat(parseFloat(lat).toFixed(6));
                this.lng = parseFloat(parseFloat(lng).toFixed(6));
                
                if (this.marker) {
                    this.marker.setLatLng([this.lat, this.lng]);
                    if (this.map) {
                        this.map.setView([this.lat, this.lng], this.map.getZoom());
                    }
                }
                
                // Обновляем через Livewire
                try {
                    if (typeof $wire !== 'undefined' && $wire) {
                        $wire.set('data.' + this.latField, this.lat);
                        $wire.set('data.' + this.lngField, this.lng);
                    }
                } catch (e) {
                    console.warn('Livewire update failed:', e);
                }
                
                // Резервный вариант - обновляем инпуты напрямую
                setTimeout(() => {
                    const latInputs = document.querySelectorAll(`input[name*=\"[${this.latField}]\"], input[name=\"data[${this.latField}]\"]`);
                    const lngInputs = document.querySelectorAll(`input[name*=\"[${this.lngField}]\"], input[name=\"data[${this.lngField}]\"]`);
                    
                    latInputs.forEach(input => {
                        input.value = this.lat;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    
                    lngInputs.forEach(input => {
                        input.value = this.lng;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }, 50);
            },
            geocodeAddress() {
                const address = this.$refs.addressInput?.value;
                if (!address) return;
                
                const button = this.$refs.searchButton;
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Поиск...';
                }
                
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&accept-language=ru`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length > 0) {
                            const { lat, lon } = data[0];
                            this.updatePosition(parseFloat(lat), parseFloat(lon));
                            if (this.$refs.addressInput) {
                                this.$refs.addressInput.value = data[0].display_name;
                            }
                        } else {
                            alert('Адрес не найден');
                        }
                    })
                    .catch(error => {
                        console.error('Geocoding error:', error);
                        alert('Ошибка при поиске адреса');
                    })
                    .finally(() => {
                        if (button) {
                            button.disabled = false;
                            button.textContent = 'Найти';
                        }
                    });
            }
        }"
        class="space-y-4"
    >
        <div class="flex gap-2">
            <input
                x-ref="addressInput"
                type="text"
                placeholder="Введите адрес для поиска на карте..."
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm px-3 py-2"
                @keyup.enter="geocodeAddress()"
            >
            <button
                x-ref="searchButton"
                type="button"
                @click="geocodeAddress()"
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
            <div class="flex items-center justify-center h-full text-gray-500" x-show="!initialized">
                <span>Загрузка карты...</span>
            </div>
        </div>
        
        <div class="flex gap-4 text-sm" x-show="initialized">
            <div class="flex-1">
                <span class="text-gray-600 font-medium">Широта:</span>
                <span class="ml-2 text-gray-900" x-text="lat ? lat.toFixed(6) : 'не указана'"></span>
            </div>
            <div class="flex-1">
                <span class="text-gray-600 font-medium">Долгота:</span>
                <span class="ml-2 text-gray-900" x-text="lng ? lng.toFixed(6) : 'не указана'"></span>
            </div>
        </div>
        
        <p class="text-xs text-gray-500">
            Кликните на карте или перетащите маркер для выбора местоположения. Координаты обновятся автоматически.
        </p>
    </div>
</x-dynamic-component>