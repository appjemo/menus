<?php

namespace App\Http\Controllers;

use App\Models\Screen;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
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

        $slots = [];

        if ($template) {
            foreach ($template->slots as $slot) {
                $product = $slot->product;

                $slots[] = [
                    'pos_x' => $slot->pos_x,
                    'pos_y' => $slot->pos_y,
                    'font_size' => $slot->font_size,
                    'font_color' => $slot->font_color,
                    'font_family' => $slot->font_family,
                    'align' => $slot->align,
                    'show_name' => (bool) $slot->show_name,
                    'name' => $product?->name ?? $slot->label,
                    'price' => $product ? number_format((float) $product->price, 2) : null,
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
