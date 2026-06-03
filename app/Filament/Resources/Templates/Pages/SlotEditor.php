<?php

namespace App\Filament\Resources\Templates\Pages;

use App\Filament\Resources\Templates\TemplateResource;
use App\Models\Template;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

/**
 * Editor visual de slots: arrastra los precios sobre el video para posicionarlos.
 */
class SlotEditor extends Page
{
    protected static string $resource = TemplateResource::class;

    protected string $view = 'filament.resources.templates.pages.slot-editor';

    public Template $record;

    public ?int $newProductId = null;

    public function mount(int|string $record): void
    {
        $this->record = Template::with(['slots.product', 'company'])->findOrFail($record);
    }

    public function getTitle(): string
    {
        return "Editor visual — {$this->record->name}";
    }

    /** Productos de la compañía dueña de la plantilla. */
    public function getProductsProperty()
    {
        return $this->record->company
            ? $this->record->company->products()->orderBy('sort_order')->get(['id', 'name', 'price'])
            : collect();
    }

    public function videoUrl(): ?string
    {
        $path = $this->record->video_path;

        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $base = rtrim((string) config('filesystems.disks.gcs.url'), '/');

        return $base.'/'.ltrim($path, '/');
    }

    /** Actualiza la posición de un slot (coordenadas en px del video base). */
    public function updatePosition(int $slotId, int $x, int $y): void
    {
        $slot = $this->record->slots()->find($slotId);

        if ($slot) {
            $slot->update([
                'pos_x' => max(0, min($x, $this->record->video_width)),
                'pos_y' => max(0, min($y, $this->record->video_height)),
            ]);
        }

        $this->record->load('slots.product');
    }

    public function addSlot(?int $productId = null): void
    {
        $this->record->slots()->create([
            'product_id' => $productId,
            'pos_x' => (int) round($this->record->video_width / 2) - 100,
            'pos_y' => (int) round($this->record->video_height / 2),
            'font_size' => 64,
            'font_color' => '#FFFFFF',
            'align' => 'left',
            'show_name' => true,
        ]);

        $this->newProductId = null;
        $this->record->load('slots.product');
    }

    public function removeSlot(int $slotId): void
    {
        $this->record->slots()->where('id', $slotId)->delete();
        $this->record->load('slots.product');
    }

    public function setFontSize(int $slotId, int $size): void
    {
        $slot = $this->record->slots()->find($slotId);

        if ($slot) {
            $slot->update(['font_size' => max(12, min($size, 300))]);
        }

        $this->record->load('slots.product');
    }
}
