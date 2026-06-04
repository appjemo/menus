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
    @include('partials.slot-effects')

    <div class="space-y-4">
        {{-- Instrucciones --}}
        <div class="flex items-center gap-2 rounded-xl bg-white p-4 text-sm text-gray-600 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:text-gray-300 dark:ring-white/10">
            <x-filament::icon icon="heroicon-o-cursor-arrow-rays" class="h-5 w-5 text-primary-500" />
            Use <strong class="font-semibold">“Add price”</strong> (top right) to place a product. Then <strong class="font-semibold">drag</strong> each price onto the video; hover over it to change size, color and font. Everything saves automatically.
        </div>

        {{-- Escenario: video + slots arrastrables --}}
        <div class="overflow-auto rounded-xl bg-gray-950 p-4">
            <div style="width: {{ $displayW }}px; margin: 0 auto;">
                <div id="slot-stage" x-data="{}" data-scale="{{ $scale }}"
                     class="rounded-lg"
                     style="position: relative; overflow: hidden; width: {{ $displayW }}px; height: {{ $displayH }}px; background: linear-gradient(135deg,#1a1a2e,#16213e);">
                    @if ($videoUrl)
                        <video style="position:absolute; inset:0; width:100%; height:100%; object-fit:fill; pointer-events:none;" autoplay loop muted playsinline>
                            <source src="{{ $videoUrl }}" type="video/mp4">
                        </video>
                    @else
                        <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:#6b7280; pointer-events:none;">No video — upload one in the template</div>
                    @endif

                    @foreach ($record->slots as $slot)
                        <div
                            wire:key="slot-{{ $slot->id }}"
                            id="slot-{{ $slot->id }}"
                            data-slot-id="{{ $slot->id }}"
                            class="slot-draggable group select-none"
                            style="position: absolute; left: {{ $slot->pos_x * $scale }}px; top: {{ $slot->pos_y * $scale }}px; cursor: grab; user-select: none;"
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
                            <div class="font-extrabold leading-tight {{ $inline ? '' : 'whitespace-nowrap' }} {{ ($slot->effect && $slot->effect !== 'none') ? $slot->effect : '' }}"
                                 style="{{ $innerStyle }}">
                                @if ($slot->show_name)
                                    <div style="font-weight:600; white-space:nowrap;">{{ $slot->product?->name ?? $slot->label ?? 'Text' }}</div>
                                @endif
                                <div style="white-space:nowrap;">${{ $slot->product ? number_format((float) $slot->product->price, 2) : '--' }}</div>
                            </div>

                            @php
                                $btn = 'border:0; border-radius:0.25rem; background:#374151; color:#fff; font-size:0.75rem; font-weight:600; padding:0.15rem 0.5rem; cursor:pointer; line-height:1.2;';
                                $ctrl = 'height:1.6rem; border:0; border-radius:0.25rem; background:#374151; color:#fff; font-size:0.75rem; cursor:pointer;';
                            @endphp
                            {{-- Controles: no inician arrastre (mousedown.stop) --}}
                            <div data-controls x-on:mousedown.stop
                                 style="position:absolute; top:-2.6rem; left:0; display:flex; align-items:center; gap:0.25rem; border-radius:0.5rem; background:rgba(17,24,39,.95); padding:0.3rem 0.4rem; box-shadow:0 4px 12px rgba(0,0,0,.4); white-space:nowrap;">
                                <button type="button" style="{{ $btn }}"
                                    wire:click="setFontSize({{ $slot->id }}, {{ max(12, $slot->font_size - 8) }})">A-</button>
                                <button type="button" style="{{ $btn }}"
                                    wire:click="setFontSize({{ $slot->id }}, {{ $slot->font_size + 8 }})">A+</button>
                                <input type="color" value="{{ $slot->font_color }}"
                                    x-on:change="$wire.setColor({{ $slot->id }}, $event.target.value)"
                                    title="Text color"
                                    style="height:1.6rem; width:1.8rem; border:0; border-radius:0.25rem; background:transparent; padding:0; cursor:pointer;">
                                <select
                                    x-on:change="$wire.setFontFamily({{ $slot->id }}, $event.target.value)"
                                    title="Font"
                                    style="{{ $ctrl }} padding:0 0.4rem; max-width:9rem;">
                                    <option value="" style="background:#374151;color:#fff;">Font…</option>
                                    @foreach (\App\Filament\Resources\Templates\Pages\SlotEditor::FONTS as $css => $label)
                                        <option value="{{ $css }}" @selected($slot->font_family === $css) style="background:#374151;color:#fff;">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <select
                                    x-on:change="$wire.setEffect({{ $slot->id }}, $event.target.value)"
                                    title="Animation effect"
                                    style="{{ $ctrl }} padding:0 0.4rem; max-width:9rem;">
                                    @foreach (\App\Filament\Resources\Templates\Pages\SlotEditor::EFFECTS as $fx => $label)
                                        <option value="{{ $fx }}" @selected(($slot->effect ?? 'none') === $fx) style="background:#374151;color:#fff;">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button type="button" style="{{ $btn }}"
                                    wire:click="toggleLayout({{ $slot->id }})"
                                    title="{{ $inline ? 'Same line' : 'Stacked' }}">{{ $inline ? '↔' : '↕' }}</button>
                                @if ($inline)
                                    <button type="button" style="{{ $btn }}" wire:click="setWidth({{ $slot->id }}, -40)" title="Narrower">W-</button>
                                    <button type="button" style="{{ $btn }}" wire:click="setWidth({{ $slot->id }}, 40)" title="Wider">W+</button>
                                @endif
                                <button type="button"
                                    style="{{ $btn }} background:#dc2626;"
                                    wire:click="removeSlot({{ $slot->id }})"
                                    title="Remove">✕</button>
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

    <script>
        // Arrastre de precios con JS puro. Guard para adjuntar listeners una sola vez
        // (sobrevive a wire:navigate y re-render de Livewire).
        (function () {
            if (window.__jemoSlotDragInit) return;
            window.__jemoSlotDragInit = true;

            let drag = null;

            document.addEventListener('mousedown', (e) => {
                const slot = e.target.closest('.slot-draggable');
                if (! slot) return;
                if (e.target.closest('[data-controls]')) return; // los controles no arrastran
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
                slot.style.zIndex = '50';
                slot.style.outline = '2px dashed rgba(255,255,255,.7)';
                e.preventDefault();
            });

            document.addEventListener('mousemove', (e) => {
                if (! drag) return;
                const stage = document.getElementById('slot-stage');
                if (! stage) return;
                const s = stage.getBoundingClientRect();
                drag.el.style.left = Math.max(0, Math.min(e.clientX - s.left - drag.offX, s.width)) + 'px';
                drag.el.style.top = Math.max(0, Math.min(e.clientY - s.top - drag.offY, s.height)) + 'px';
            });

            document.addEventListener('mouseup', () => {
                if (! drag) return;
                const baseX = Math.round(parseFloat(drag.el.style.left) / drag.scale);
                const baseY = Math.round(parseFloat(drag.el.style.top) / drag.scale);
                drag.el.style.cursor = 'grab';
                drag.el.style.outline = '';
                const id = drag.id;
                const root = drag.el.closest('[wire\\:id]');
                drag = null;
                const wireId = root && root.getAttribute('wire:id');
                if (wireId && window.Livewire) {
                    window.Livewire.find(wireId).call('updatePosition', id, baseX, baseY);
                }
            });
        })();
    </script>
</x-filament-panels::page>
