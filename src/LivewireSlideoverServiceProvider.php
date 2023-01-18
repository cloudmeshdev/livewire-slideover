<?php

namespace LivewireUI\Slideover;

use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LivewireSlideoverServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('livewire-ui-slideover')
            ->hasConfigFile()
            ->hasViews();
    }

    public function bootingPackage(): void
    {
        Livewire::component('livewire-ui-slideover', Slideover::class);
    }
}
