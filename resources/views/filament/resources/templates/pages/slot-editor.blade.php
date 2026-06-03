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
        {{-- Barra de herramientas --}}
        <div class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm dark:bg-gray-900">
            <div class="flex-1 min-w-64">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Agregar precio de un producto</label>
                <select wire:model="newProductId"
                    class="mt-1 block w-full rounded-lg border-gray-300 text-sm dark:bg-gray-800 dark:border-gray-700">
                    <option value="">— Selecciona un producto —</option>
                    @foreach ($this->products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} (${{ number_format((float) $p->price, 2) }})</option>
                    @endforeach
                </select>
            </div>
            <x-filament::button wire:click="addSlot($wire.newProductId)" icon="heroicon-o-plus">
                Agregar al video
            </x-filament::button>
            <span class="text-sm text-gray-500">Arrastra cada precio para posicionarlo. Se guarda solo.</span>
        </div>

        {{-- Escenario: video + slots arrastrables --}}
        <div class="overflow-auto rounded-xl bg-gray-950 p-4">
            <div
                x-data="{
                    scale: {{ $scale }},
                    drag: null,
                    start(e, id) {
                        const el = e.currentTarget;
                        el.setPointerCapture(e.pointerId);
                        const r = el.getBoundingClientRect();
                        this.drag = { el, offsetX: e.clientX - r.left, offsetY: e.clientY - r.top };
                        el.style.cursor = 'grabbing';
                    },
                    move(e) {
                        if (! this.drag) return;
                        const stage = this.$refs.stage.getBoundingClientRect();
                        let x = e.clientX - stage.left - this.drag.offsetX;
                        let y = e.clientY - stage.top - this.drag.offsetY;
                        x = Math.max(0, Math.min(x, stage.width));
                        y = Math.max(0, Math.min(y, stage.height));
                        this.drag.el.style.left = x + 'px';
                        this.drag.el.style.top = y + 'px';
                    },
                    end(e, id) {
                        if (! this.drag) return;
                        const baseX = Math.round(parseFloat(this.drag.el.style.left) / this.scale);
                        const baseY = Math.round(parseFloat(this.drag.el.style.top) / this.scale);
                        this.drag.el.style.cursor = 'grab';
                        this.drag = null;
                        $wire.updatePosition(id, baseX, baseY);
                    },
                }"
                class="relative mx-auto"
                style="width: {{ $displayW }}px; height: {{ $displayH }}px;"
            >
                <div x-ref="stage" class="absolute inset-0 overflow-hidden rounded-lg"
                     style="background: linear-gradient(135deg,#1a1a2e,#16213e);">
                    @if ($videoUrl)
                        <video class="absolute inset-0 h-full w-full object-fill" autoplay loop muted playsinline>
                            <source src="{{ $videoUrl }}" type="video/mp4">
                        </video>
                    @else
                        <div class="flex h-full w-full items-center justify-center text-gray-500">Sin video — sube uno en la plantilla</div>
                    @endif

                    @foreach ($record->slots as $slot)
                        <div
                            wire:key="slot-{{ $slot->id }}"
                            x-on:pointerdown="start($event, {{ $slot->id }})"
                            x-on:pointermove="move($event)"
                            x-on:pointerup="end($event, {{ $slot->id }})"
                            class="group absolute select-none touch-none"
                            style="left: {{ $slot->pos_x * $scale }}px; top: {{ $slot->pos_y * $scale }}px; cursor: grab;"
                        >
                            <div class="whitespace-nowrap font-extrabold leading-tight"
                                 style="font-size: {{ max(8, $slot->font_size * $scale) }}px; color: {{ $slot->font_color }}; text-shadow: 0 2px 6px rgba(0,0,0,.7);">
                                @if ($slot->show_name)
                                    <div style="font-weight:600;">{{ $slot->product?->name ?? $slot->label ?? 'Texto' }}</div>
                                @endif
                                <div>${{ $slot->product ? number_format((float) $slot->product->price, 2) : '--' }}</div>
                            </div>

                            {{-- Controles (aparecen al pasar el mouse) --}}
                            <div class="absolute -top-7 left-0 hidden gap-1 group-hover:flex">
                                <button type="button" wire:click="setFontSize({{ $slot->id }}, {{ max(12, $slot->font_size - 8) }})"
                                    class="rounded bg-gray-800 px-2 text-xs text-white">A-</button>
                                <button type="button" wire:click="setFontSize({{ $slot->id }}, {{ $slot->font_size + 8 }})"
                                    class="rounded bg-gray-800 px-2 text-xs text-white">A+</button>
                                <button type="button" wire:click="removeSlot({{ $slot->id }})"
                                    class="rounded bg-red-600 px-2 text-xs text-white">✕</button>
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
