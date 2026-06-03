@php
    use App\Filament\Resources\Products\ProductResource;
    use App\Filament\Resources\Templates\TemplateResource;
    use App\Filament\Resources\Screens\ScreenResource;
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Quick actions</x-slot>

        <div class="flex flex-wrap gap-3">
            <x-filament::button tag="a" :href="ProductResource::getUrl('index')" icon="heroicon-o-currency-dollar" color="primary">
                Edit prices
            </x-filament::button>

            <x-filament::button tag="a" :href="TemplateResource::getUrl('index')" icon="heroicon-o-cursor-arrow-rays" color="gray">
                Templates / Visual editor
            </x-filament::button>

            <x-filament::button tag="a" :href="ScreenResource::getUrl('index')" icon="heroicon-o-tv" color="gray">
                Screens
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
