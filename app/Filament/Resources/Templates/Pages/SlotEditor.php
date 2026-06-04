<?php

namespace App\Filament\Resources\Templates\Pages;

use App\Filament\Resources\Templates\TemplateResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

/**
 * Editor visual de slots: arrastra los precios sobre el video para posicionarlos.
 */
class SlotEditor extends Page
{
    use InteractsWithRecord;

    protected static string $resource = TemplateResource::class;

    protected string $view = 'filament.resources.templates.pages.slot-editor';

    public static function canAccess(array $parameters = []): bool
    {
        return (bool) auth()->user()?->can('template.update');
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->load(['slots.product', 'company']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addSlot')
                ->label('Add price')
                ->icon('heroicon-o-plus')
                ->modalHeading('Add a price to the video')
                ->modalSubmitActionLabel('Add')
                ->schema([
                    Select::make('product_id')
                        ->label('Product')
                        ->options(fn () => $this->record->company
                            ? $this->record->company->products()->orderBy('sort_order')->pluck('name', 'id')
                            : [])
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->action(fn (array $data) => $this->addSlot((int) $data['product_id'])),
        ];
    }

    public function getTitle(): string
    {
        return "Visual editor — {$this->record->name}";
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

    public function setColor(int $slotId, string $color): void
    {
        // Solo aceptar hex tipo #RRGGBB
        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            return;
        }

        $slot = $this->record->slots()->find($slotId);

        if ($slot) {
            $slot->update(['font_color' => strtoupper($color)]);
        }

        $this->record->load('slots.product');
    }

    public function setFontFamily(int $slotId, ?string $family): void
    {
        $allowed = array_keys(self::FONTS);

        $slot = $this->record->slots()->find($slotId);

        if ($slot) {
            $slot->update([
                'font_family' => in_array($family, $allowed, true) ? $family : null,
            ]);
        }

        $this->record->load('slots.product');
    }

    /** Tipografías disponibles: clave = valor CSS (con comillas simples), valor = etiqueta. */
    public const FONTS = [
        // Google Fonts (se cargan en el editor y el Player; cacheadas para offline)
        "'Anton', sans-serif" => 'Anton',
        "'Bebas Neue', sans-serif" => 'Bebas Neue',
        "'Oswald', sans-serif" => 'Oswald',
        "'Montserrat', sans-serif" => 'Montserrat',
        "'Poppins', sans-serif" => 'Poppins',
        "'Roboto', sans-serif" => 'Roboto',
        "'Roboto Condensed', sans-serif" => 'Roboto Condensed',
        "'Teko', sans-serif" => 'Teko',
        "'Archivo Black', sans-serif" => 'Archivo Black',
        "'Fjalla One', sans-serif" => 'Fjalla One',
        "'Lobster', cursive" => 'Lobster',
        "'Pacifico', cursive" => 'Pacifico',
        "'Bangers', cursive" => 'Bangers',
        // Tipografías del sistema (siempre disponibles)
        'Arial, sans-serif' => 'Arial',
        'Georgia, serif' => 'Georgia',
        "'Times New Roman', serif" => 'Times New Roman',
        'Verdana, sans-serif' => 'Verdana',
        'Impact, sans-serif' => 'Impact',
        "'Courier New', monospace" => 'Courier New',
    ];

    /** URL de Google Fonts con todas las familias cargadas (editor + Player). */
    public const GOOGLE_FONTS_HREF = 'https://fonts.googleapis.com/css2?family=Anton&family=Archivo+Black&family=Bangers&family=Bebas+Neue&family=Fjalla+One&family=Lobster&family=Montserrat:wght@400;600;700;800&family=Oswald:wght@400;500;700&family=Pacifico&family=Poppins:wght@400;600;700;800&family=Roboto:wght@400;700;900&family=Roboto+Condensed:wght@400;700&family=Teko:wght@400;500;700&display=swap';
}
