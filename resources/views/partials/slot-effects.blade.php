{{-- Animaciones de los precios/slots. Incluido tanto en el editor como en el Player. --}}
<style>
    /* ===== Efectos en loop (continuos) ===== */
    @keyframes fx-pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.08); } }
    .fx-pulse { animation: fx-pulse 1.5s ease-in-out infinite; }

    @keyframes fx-glow {
        0%,100% { text-shadow: 0 2px 6px rgba(0,0,0,.7); }
        50%     { text-shadow: 0 0 18px rgba(255,255,255,.95), 0 2px 6px rgba(0,0,0,.7); }
    }
    .fx-glow { animation: fx-glow 1.8s ease-in-out infinite; }

    @keyframes fx-blink { 0%,100% { opacity: 1; } 50% { opacity: .25; } }
    .fx-blink { animation: fx-blink 1.1s ease-in-out infinite; }

    @keyframes fx-bounce { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-14px); } }
    .fx-bounce { animation: fx-bounce 1s ease-in-out infinite; }

    @keyframes fx-float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }
    .fx-float { animation: fx-float 3s ease-in-out infinite; }

    @keyframes fx-shake {
        0%,100% { transform: translateX(0); }
        20% { transform: translateX(-4px); } 40% { transform: translateX(4px); }
        60% { transform: translateX(-3px); } 80% { transform: translateX(3px); }
    }
    .fx-shake { animation: fx-shake .6s ease-in-out infinite; }

    @keyframes fx-tada {
        0% { transform: scale(1) rotate(0); }
        10%,20% { transform: scale(.95) rotate(-3deg); }
        30%,50%,70%,90% { transform: scale(1.1) rotate(3deg); }
        40%,60%,80% { transform: scale(1.1) rotate(-3deg); }
        100% { transform: scale(1) rotate(0); }
    }
    .fx-tada { animation: fx-tada 2.2s ease-in-out infinite; }

    @keyframes fx-swing {
        0%,100% { transform: rotate(0deg); }
        25% { transform: rotate(4deg); } 75% { transform: rotate(-4deg); }
    }
    .fx-swing { animation: fx-swing 1.6s ease-in-out infinite; transform-origin: top center; }

    /* ===== Efectos de entrada (una sola vez) ===== */
    @keyframes fx-fade-in { from { opacity: 0; } to { opacity: 1; } }
    .fx-fade-in { animation: fx-fade-in .9s ease-out 1; }

    @keyframes fx-slide-up { from { opacity: 0; transform: translateY(35px); } to { opacity: 1; transform: translateY(0); } }
    .fx-slide-up { animation: fx-slide-up .8s ease-out 1; }

    @keyframes fx-slide-left { from { opacity: 0; transform: translateX(60px); } to { opacity: 1; transform: translateX(0); } }
    .fx-slide-left { animation: fx-slide-left .8s ease-out 1; }

    @keyframes fx-zoom-in { from { opacity: 0; transform: scale(.4); } to { opacity: 1; transform: scale(1); } }
    .fx-zoom-in { animation: fx-zoom-in .7s cubic-bezier(.2,.8,.2,1.2) 1; }
</style>
