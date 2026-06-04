{{-- Estilos de marca JEMO: login moderno (layout "simple" de Filament) --}}
<style>
    /* Fondo degradado con acento ámbar en la pantalla de login */
    .fi-simple-layout {
        background:
            radial-gradient(1100px 560px at 50% -12%, rgba(245, 158, 11, .14), transparent 60%),
            linear-gradient(160deg, #0b1220 0%, #111827 55%, #0b1220 100%) !important;
    }

    /* Tarjeta del formulario con realce */
    .fi-simple-main {
        border: 1px solid rgba(255, 255, 255, .08) !important;
        border-radius: 1rem !important;
        box-shadow: 0 24px 70px -20px rgba(0, 0, 0, .7) !important;
    }

    /* Logo más grande y centrado en el login */
    .fi-simple-layout .fi-logo {
        height: 3.25rem !important;
        margin-inline: auto;
    }

    /* Botón principal un poco más llamativo */
    .fi-simple-main .fi-btn-color-primary {
        font-weight: 700;
        letter-spacing: .02em;
    }
</style>
