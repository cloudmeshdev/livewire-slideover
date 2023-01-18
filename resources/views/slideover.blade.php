<div>
    @php
        $map = [];
    
        foreach ($components as $id => $component) {
            $map[] = $id;
        }
    @endphp

    @isset($jsPath)
        <script>{!! file_get_contents($jsPath) !!}</script>
    @endisset
    @isset($cssPath)
        <style>{!! file_get_contents($cssPath) !!}</style>
    @endisset

    <div
        x-data="LivewireUISlideover()"
        x-init="init()"
        {{-- x-on:close.stop="setShowPropertyTo(false)" --}}
        x-on:keydown.escape.window="closeSlideoverOnEscape()"
        x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
        x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
        x-show="activeComponents.length > 0"
        class="fixed inset-0 z-10 overflow-y-auto"
        style="display: none;"
    >

        @for ($i = 0; $i < 2; $i++)
            <!-- Slideover template -->
            <div 
                x-show="show && activeComponents.length > {{ $i }}" 
                class="fixed inset-0 overflow-hidden z-10"
            >
                <div 
                    class="absolute inset-0 bg-gray-500 bg-opacity-75 z-10"
                    x-show="show && activeComponents.length > {{ $i }}" 
                    x-transition:enter="ease-in-out duration-500" 
                    x-transition:enter-start="opacity-0" 
                    x-transition:enter-end="opacity-100" 
                    x-transition:leave="ease-in-out duration-500"
                    x-transition:leave-start="opacity-100" 
                    x-transition:leave-end="opacity-0"
                    aria-hidden="true" 
                    x-description="Background overlay, show/hide based on slide-over state." 
                ></div>
                
                <div 
                    x-show="show && activeComponents.length > {{ $i }}" 
                    class="absolute inset-y-0 right-0 bg-white z-10"
                    x-bind:class="getComponentAttributeById(getComponentIdByIndex({{ $i }}), 'width')"
                    x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700" 
                    x-transition:enter-start="translate-x-full" 
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" 
                    x-transition:leave-start="translate-x-0" 
                    x-transition:leave-end="translate-x-full"
                    x-description=" Slide-over panel, show/hide based on slide-over state." 
                >
                    <button class="bg-red-100 rounded p-2" x-on:click="Livewire.emit('closeSlideover')">x</button>

                    @if (count($components) > $i)
                        @php
                            $componentId = collect($components)->slice($i, 1)->keys()->first();
                            $component = $components[$componentId];
                        @endphp
                        
                        @livewire($component['name'], $component['attributes'], key($id))
                    @endif
                </div>
            </div>
            <!-- Slideover template -->

        @endfor
    
    </div>
</div>
