@php
    $videoW = max(1, (int) $record->video_width);
    $videoH = max(1, (int) $record->video_height);
    $displayW = min($videoW, 960);
    $scale = $displayW / $videoW;
    $displayH = $videoH * $scale;
    $videoUrl = $this->videoUrl();
@endphp

<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Instrucciones --}}
        <div class="flex items-center gap-2 rounded-xl bg-white p-4 text-sm text-gray-600 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-300 dark:ring-white/10">
            <x-filament::icon icon="heroicon-o-cursor-arrow-rays" class="h-5 w-5 text-primary-500" />
            Usa <strong class="font-semibold">“Agregar precio”</strong> (arriba a la derecha) para colocar un producto. Luego <strong class="font-semibold">arrastra</strong> cada precio sobre el video; al pasar el cursor por encima puedes cambiar tamaño, color y tipografía. Todo se guarda solo.
        </div>

        {{-- Escenario: video + slots arrastrables --}}
        <div class="overflow-auto rounded-xl bg-gray-950 p-4">
            <div
                x-data="{ scale: {{ $scale }}, el: null, id: null, sx: 0, sy: 0 }"
                x-on:mousemove.window="
                    if (el) {
                        const s = $refs.stage.getBoundingClientRect();
                        el.style.left = Math.max(0, Math.min($event.clientX - s.left - sx, s.width)) + 'px';
                        el.style.top = Math.max(0, Math.min($event.clientY - s.top - sy, s.height)) + 'px';
                    }
                "
                x-on:mouseup.window="
                    if (el) {
                        const x = Math.round(parseFloat(el.style.left) / scale);
                        const y = Math.round(parseFloat(el.style.top) / scale);
                        el.style.cursor = 'grab';
                        el = null;
                        $wire.updatePosition(id, x, y);
                    }
                "
                class="relative mx-auto"
                style="width: {{ $displayW }}px; height: {{ $displayH }}px;"
            >
                <div x-ref="stage" class="absolute inset-0 overflow-hidden rounded-lg"
                     style="background: linear-gradient(135deg,#1a1a2e,#16213e);">
                    @if ($videoUrl)
                        <video class="pointer-events-none absolute inset-0 h-full w-full object-fill" autoplay loop muted playsinline>
                            <source src="{{ $videoUrl }}" type="video/mp4">
                        </video>
                    @else
                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center text-gray-500">Sin video — sube uno en la plantilla</div>
                    @endif

                    @foreach ($record->slots as $slot)
                        <div
                            wire:key="slot-{{ $slot->id }}"
                            x-on:mousedown="
                                if ($event.target.closest('[data-controls]')) return;
                                el = $el; id = {{ $slot->id }};
                                const r = $el.getBoundingClientRect();
                                sx = $event.clientX - r.left;
                                sy = $event.clientY - r.top;
                                $el.style.cursor = 'grabbing';
                                $event.preventDefault();
                            "
                            class="group absolute select-none"
                            style="left: {{ $slot->pos_x * $scale }}px; top: {{ $slot->pos_y * $scale }}px; cursor: grab;"
                        >
                            <div class="whitespace-nowrap font-extrabold leading-tight"
                                 style="font-size: {{ max(8, $slot->font_size * $scale) }}px; color: {{ $slot->font_color }}; font-family: {{ $slot->font_family ?: 'inherit' }}; text-shadow: 0 2px 6px rgba(0,0,0,.7);">
                                @if ($slot->show_name)
                                    <div style="font-weight:600;">{{ $slot->product?->name ?? $slot->label ?? 'Texto' }}</div>
                                @endif
                                <div>${{ $slot->product ? number_format((float) $slot->product->price, 2) : '--' }}</div>
                            </div>

                            {{-- Controles: no inician arrastre (mousedown.stop) --}}
                            <div data-controls x-on:mousedown.stop
                                 class="absolute -top-10 left-0 flex items-center gap-1 rounded-lg bg-gray-900/95 px-1.5 py-1 opacity-0 shadow-lg ring-1 ring-white/10 transition group-hover:opacity-100">
                                <button type="button"
                                    wire:click="setFontSize({{ $slot->id }}, {{ max(12, $slot->font_size - 8) }})"
                                    class="rounded bg-gray-700 px-2 py-0.5 text-xs font-semibold text-white hover:bg-gray-600">A-</button>
                                <button type="button"
                                    wire:click="setFontSize({{ $slot->id }}, {{ $slot->font_size + 8 }})"
                                    class="rounded bg-gray-700 px-2 py-0.5 text-xs font-semibold text-white hover:bg-gray-600">A+</button>
                                <input type="color" value="{{ $slot->font_color }}"
                                    x-on:change="$wire.setColor({{ $slot->id }}, $event.target.value)"
                                    title="Color del texto"
                                    class="h-6 w-6 cursor-pointer rounded border-0 bg-transparent p-0">
                                <select
                                    x-on:change="$wire.setFontFamily({{ $slot->id }}, $event.target.value)"
                                    title="Tipografía"
                                    class="h-6 rounded border-0 bg-gray-700 py-0 pl-1 pr-5 text-xs text-white">
                                    <option value="">Fuente…</option>
                                    @foreach (\App\Filament\Resources\Templates\Pages\SlotEditor::FONTS as $css => $label)
                                        <option value="{{ $css }}" @selected($slot->font_family === $css)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    wire:click="removeSlot({{ $slot->id }})"
                                    class="rounded bg-red-600 px-2 py-0.5 text-xs font-semibold text-white hover:bg-red-500">✕</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-500">
            Resolución base del video: {{ $videoW }}×{{ $videoH }} px. Las posiciones se guardan en esas coordenadas y se escalan en cada pantalla.
        </p>
    </div>
</x-filament-panels::page>
