<?php

namespace LivewireUI\Slideover;

use Exception;
use Illuminate\View\View;
use Livewire\Component;
use ReflectionClass;

class Slideover extends Component
{
    public ?string $activeComponent;

    public array $components = [];

    public function resetState(): void
    {
        $this->components = [];
        $this->activeComponent = null;
    }

    public function openSlideover($component, $componentAttributes = [], $slideoverAttributes = []): void
    {
        $requiredInterface = \LivewireUI\Slideover\Contracts\SlideoverComponent::class;
        $componentClass = app('livewire')->getClass($component);
        $reflect = new ReflectionClass($componentClass);

        if ($reflect->implementsInterface($requiredInterface) === false) {
            throw new Exception("[{$componentClass}] does not implement [{$requiredInterface}] interface.");
        }

        $id = md5($component . serialize($componentAttributes));
        $this->components[$id] = [
            'name'            => $component,
            'attributes'      => $componentAttributes,
            'slideoverAttributes' => array_merge([
                'slideoverCloseOnClickAway' => $componentClass::closeSlideoverOnClickAway(),
                'slideoverCloseOnEscape' => $componentClass::closeSlideoverOnEscape(),
                'slideoverCloseOnEscapeIsForceful' => $componentClass::closeSlideoverOnEscapeIsForceful(),
                'dispatchSlideoverCloseEvent' => $componentClass::dispatchSlideoverCloseEvent(),
                'destroySlideoverOnClose' => $componentClass::destroySlideoverOnClose(),
                'width' => $componentClass::width(),
            ], $slideoverAttributes),
        ];

        $this->activeComponent = $id;

        $this->emit('activeSlideoverComponentChanged', $id);
    }

    public function destroyComponent($id): void
    {
        unset($this->components[$id]);
    }

    public function getListeners(): array
    {
        return [
            'openSlideover',
            'destroyComponent'
        ];
    }

    public function render(): View
    {
        if (config('livewire-ui-slideover.include_js', true)) {
            $jsPath = __DIR__ . '/../public/slideover.js';
        }

        if (config('livewire-ui-slideover.include_css', false)) {
            $cssPath = __DIR__ . '/../public/slideover.css';
        }

        return view('livewire-ui-slideover::slideover', [
            'jsPath' => $jsPath ?? null,
            'cssPath' => $cssPath ?? null,
        ]);
    }
}
