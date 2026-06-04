@php
    $videoW = max(1, (int) $record->video_width);
    $videoH = max(1, (int) $record->video_height);
    $displayW = min($videoW, 960);
    $scale = $displayW / $videoW;
    $displayH = $videoH * $scale;
    $videoUrl = $this->videoUrl();
@endphp

<x-filament-panels::page>
    <link href="{{ \App\Filament\Resources\Templates\Pages\SlotEditor::GOOGLE_FONTS_HREF }}" rel="stylesheet">

    <div class="space-y-4">
        {{-- Instrucciones --}}
        <div class="flex items-center gap-2 rounded-xl bg-white p-4 text-sm text-gray-600 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-300 dark:ring-white/10">
            <x-filament::icon icon="heroicon-o-cursor-arrow-rays" class="h-5 w-5 text-primary-500" />
            Use <strong class="font-semibold">“Add price”</strong> (top right) to place a product. Then <strong class="font-semibold">drag</strong> each price onto the video; hover over it to change size, color and font. Everything saves automatically.
        </div>

        {{-- Escenario: video + slots arrastrables --}}
        <div class="overflow-auto rounded-xl bg-gray-950 p-4">
            <div
                x-data="{}"
                class="relative mx-auto"
                style="width: {{ $displayW }}px; height: {{ $displayH }}px;"
            >
                <div id="slot-stage" data-scale="{{ $scale }}"
                     class="absolute inset-0 overflow-hidden rounded-lg"
                     style="background: linear-gradient(135deg,#1a1a2e,#16213e);">
                    @if ($videoUrl)
                        <video class="pointer-events-none absolute inset-0 h-full w-full object-fill" autoplay loop muted playsinline>
                            <source src="{{ $videoUrl }}" type="video/mp4">
                        </video>
                    @else
                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center text-gray-500">No video — upload one in the template</div>
                    @endif

                    @foreach ($record->slots as $slot)
                        <div
                            wire:key="slot-{{ $slot->id }}"
                            id="slot-{{ $slot->id }}"
                            data-slot-id="{{ $slot->id }}"
                            class="slot-draggable group absolute select-none"
                            style="left: {{ $slot->pos_x * $scale }}px; top: {{ $slot->pos_y * $scale }}px; cursor: grab;"
                        >
                            @php
                                $inline = ($slot->layout ?? 'stacked') === 'inline';
                                $innerStyle = 'font-size: '.max(8, $slot->font_size * $scale).'px; color: '.$slot->font_color.'; font-family: '.($slot->font_family ?: 'inherit').'; text-shadow: 0 2px 6px rgba(0,0,0,.7);';
                                if ($inline) {
                                    $innerStyle .= ' display:flex; align-items:baseline;';
                                    $innerStyle .= $slot->box_width
                                        ? ' width:'.($slot->box_width * $scale).'px; justify-content:space-between;'
                                        : ' gap:0.5em;';
                                }
                            @endphp
                            <div class="font-extrabold leading-tight {{ $inline ? '' : 'whitespace-nowrap' }}"
                                 style="{{ $innerStyle }}">
                                @if ($slot->show_name)
                                    <div style="font-weight:600; white-space:nowrap;">{{ $slot->product?->name ?? $slot->label ?? 'Text' }}</div>
                                @endif
                                <div style="white-space:nowrap;">${{ $slot->product ? number_format((float) $slot->product->price, 2) : '--' }}</div>
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
                                    title="Text color"
                                    class="h-6 w-6 cursor-pointer rounded border-0 bg-transparent p-0">
                                <select
                                    x-on:change="$wire.setFontFamily({{ $slot->id }}, $event.target.value)"
                                    title="Font"
                                    class="h-6 rounded border-0 bg-gray-700 py-0 pl-1 pr-5 text-xs text-white">
                                    <option value="">Font…</option>
                                    @foreach (\App\Filament\Resources\Templates\Pages\SlotEditor::FONTS as $css => $label)
                                        <option value="{{ $css }}" @selected($slot->font_family === $css)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="button"
                                    wire:click="toggleLayout({{ $slot->id }})"
                                    title="{{ $inline ? 'Same line' : 'Stacked' }}"
                                    class="rounded bg-gray-700 px-2 py-0.5 text-xs font-semibold text-white hover:bg-gray-600">{{ $inline ? '↔' : '↕' }}</button>
                                @if ($inline)
                                    <button type="button" wire:click="setWidth({{ $slot->id }}, -40)" title="Narrower"
                                        class="rounded bg-gray-700 px-2 py-0.5 text-xs font-semibold text-white hover:bg-gray-600">W-</button>
                                    <button type="button" wire:click="setWidth({{ $slot->id }}, 40)" title="Wider"
                                        class="rounded bg-gray-700 px-2 py-0.5 text-xs font-semibold text-white hover:bg-gray-600">W+</button>
                                @endif
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
            Base video resolution: {{ $videoW }}×{{ $videoH }} px. Positions are saved in those coordinates and scaled on each screen.
        </p>
    </div>

    @script
    <script>
        // Arrastre de precios con JS puro (delegación a nivel document; robusto ante re-render de Livewire)
        let drag = null;

        document.addEventListener('mousedown', (e) => {
            const slot = e.target.closest('.slot-draggable');
            if (! slot) return;
            if (e.target.closest('[data-controls]')) return; // no arrastrar al usar los controles
            const stage = document.getElementById('slot-stage');
            if (! stage) return;
            const r = slot.getBoundingClientRect();
            drag = {
                el: slot,
                id: parseInt(slot.dataset.slotId),
                scale: parseFloat(stage.dataset.scale || '1'),
                offX: e.clientX - r.left,
                offY: e.clientY - r.top,
            };
            slot.style.cursor = 'grabbing';
            e.preventDefault();
        });

        document.addEventListener('mousemove', (e) => {
            if (! drag) return;
            const stage = document.getElementById('slot-stage');
            const s = stage.getBoundingClientRect();
            const x = Math.max(0, Math.min(e.clientX - s.left - drag.offX, s.width));
            const y = Math.max(0, Math.min(e.clientY - s.top - drag.offY, s.height));
            drag.el.style.left = x + 'px';
            drag.el.style.top = y + 'px';
        });

        document.addEventListener('mouseup', () => {
            if (! drag) return;
            const baseX = Math.round(parseFloat(drag.el.style.left) / drag.scale);
            const baseY = Math.round(parseFloat(drag.el.style.top) / drag.scale);
            drag.el.style.cursor = 'grab';
            const id = drag.id;
            drag = null;
            $wire.updatePosition(id, baseX, baseY);
        });
    </script>
    @endscript
</x-filament-panels::page>
