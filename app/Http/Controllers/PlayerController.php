<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\Screen;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PlayerController extends Controller
{
    /**
     * Página del Player que abre el Raspberry en la TV: /play/{token}
     */
    public function show(string $token)
    {
        $screen = Screen::where('token', $token)->firstOrFail();

        $this->touchHeartbeat($screen);

        return view('player', [
            'screen' => $screen,
            'menu' => $this->buildMenu($screen),
            'reverbKey' => config('broadcasting.connections.reverb.key'),
        ]);
    }

    /**
     * JSON del menú actual de la pantalla (lo consume el Player al recibir el evento).
     */
    public function menu(string $token): JsonResponse
    {
        $screen = Screen::where('token', $token)->firstOrFail();

        $this->touchHeartbeat($screen);

        return response()->json($this->buildMenu($screen));
    }

    /**
     * Arma el payload del menú: video de fondo + slots con precios resueltos.
     */
    private function buildMenu(Screen $screen): array
    {
        $screen->loadMissing('template.slots.product');
        $template = $screen->template;

        // Active promotions for this company, keyed by product (best deal first)
        $promosByProduct = $this->activePromotions($screen->company_id);

        $slots = [];

        if ($template) {
            foreach ($template->slots as $slot) {
                $product = $slot->product;

                $price = $product?->price;
                $originalPrice = null;
                $isPromo = false;

                if ($product) {
                    $promo = $promosByProduct->get($product->id)?->first();
                    if ($promo) {
                        $originalPrice = $product->price;
                        $price = $promo->promo_price;
                        $isPromo = true;
                    }
                }

                $slots[] = [
                    'pos_x' => $slot->pos_x,
                    'pos_y' => $slot->pos_y,
                    'font_size' => $slot->font_size,
                    'font_color' => $slot->font_color,
                    'font_family' => $slot->font_family,
                    'align' => $slot->align,
                    'show_name' => (bool) $slot->show_name,
                    'layout' => $slot->layout ?? 'stacked',
                    'box_width' => $slot->box_width,
                    'effect' => $slot->effect ?? 'none',
                    'name' => $product?->name ?? $slot->label,
                    'price' => $price !== null ? number_format((float) $price, 2) : null,
                    'original_price' => $originalPrice !== null ? number_format((float) $originalPrice, 2) : null,
                    'is_promo' => $isPromo,
                ];
            }
        }

        return [
            'company_id' => $screen->company_id,
            'screen' => $screen->name,
            'template' => $template ? [
                'video_url' => $this->videoUrl($template->video_path),
                'width' => $template->video_width,
                'height' => $template->video_height,
            ] : null,
            'slots' => $slots,
            'generated_at' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Promociones vigentes de la compañía, agrupadas por product_id y ordenadas
     * por mejor precio (la primera de cada grupo es la de menor promo_price).
     */
    private function activePromotions(int $companyId): Collection
    {
        $now = Carbon::now();

        return Promotion::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNotNull('product_id')
            ->whereNotNull('promo_price')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderBy('promo_price')
            ->get()
            ->groupBy('product_id');
    }

    /**
     * URL pública del video. Si ya es una URL completa la usa; si es una clave
     * en GCS, construye la URL pública del bucket.
     */
    private function videoUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        // URL pública del bucket (uniform access + IAM allUsers:objectViewer)
        $base = rtrim((string) config('filesystems.disks.gcs.url'), '/');

        return $base.'/'.ltrim($path, '/');
    }

    private function touchHeartbeat(Screen $screen): void
    {
        // Marca "vista por última vez" sin disparar eventos de modelo.
        $screen->forceFill(['last_seen_at' => Carbon::now()])->saveQuietly();
    }
}
