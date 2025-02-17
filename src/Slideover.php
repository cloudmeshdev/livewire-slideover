<?php

namespace LivewireUI\Slideover;

use Exception;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Reflector;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Mechanisms\ComponentRegistry;
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

    public function openSlideover($component, $arguments = [], $slideoverAttributes = []): void
    {
        $requiredInterface = \LivewireUI\Slideover\Contracts\SlideoverComponent::class;
        $componentClass = app(ComponentRegistry::class)->getClass($component);
        $reflect = new ReflectionClass($componentClass);

        if ($reflect->implementsInterface($requiredInterface) === false) {
            throw new Exception("[{$componentClass}] does not implement [{$requiredInterface}] interface.");
        }

        $id = md5($component.serialize($arguments));

        $arguments = collect($arguments)
            ->merge($this->resolveComponentProps($arguments, new $componentClass()))
            ->all();


        $this->components[$id] = [
            'name' => $component,
            'attributes' => $arguments, // Deprecated
            'arguments' => $arguments,
            'slideoverAttributes' => array_merge([
                'closeOnClickAway' => $componentClass::closeSlideoverOnClickAway(),
                'closeOnEscape' => $componentClass::closeSlideoverOnEscape(),
                'closeOnEscapeIsForceful' => $componentClass::closeSlideoverOnEscapeIsForceful(),
                'dispatchCloseEvent' => $componentClass::dispatchCloseEvent(),
                'destroyOnClose' => $componentClass::destroyOnClose(),
                'width' => $componentClass::slideoverWidth(),
                'maxWidth' => $componentClass::slideoverMaxWidth(),
                'maxWidthClass' => $componentClass::slideoverMaxWidthClass(),
            ], $slideoverAttributes),
        ];

        $this->activeComponent = $id;

        $this->dispatch('activeSlideoverComponentChanged', id: $id);
    }

    public function resolveComponentProps(array $attributes, Component $component)
    {
        if (PHP_VERSION_ID < 70400) {
            return;
        }

        return $this->getPublicPropertyTypes($component)
            ->intersectByKeys($attributes)
            ->map(function ($className, $propName) use ($attributes) {
                $resolved = $this->resolveParameter($attributes, $propName, $className);

                return $resolved;
            });
    }

    protected function resolveParameter($attributes, $parameterName, $parameterClassName)
    {
        $parameterValue = $attributes[$parameterName];

        if ($parameterValue instanceof UrlRoutable) {
            return $parameterValue;
        }

        $instance = app()->make($parameterClassName);

        if (! $model = $instance->resolveRouteBinding($parameterValue)) {
            throw (new ModelNotFoundException())->setModel(get_class($instance), [$parameterValue]);
        }

        return $model;
    }

    public function getPublicPropertyTypes($component)
    {
        if (PHP_VERSION_ID < 70400) {
            return new Collection();
        }

        return collect($component->all())
            ->map(function ($value, $name) use ($component) {
                return Reflector::getParameterClassName(new \ReflectionProperty($component, $name));
            })
            ->filter();
    }

    public function destroyComponent($id): void
    {
        unset($this->components[$id]);
    }

    public function getListeners(): array
    {
        return [
            'openSlideover',
            'destroyComponent',
        ];
    }

    public function render(): View
    {
        if (config('livewire-ui-slideover.include_js', true)) {
            $jsPath = __DIR__.'/../public/slideover.js';
        }

        if (config('livewire-ui-slideover.include_css', false)) {
            $cssPath = __DIR__.'/../public/slideover.css';
        }

        return view('livewire-ui-slideover::slideover', [
            'jsPath' => $jsPath ?? null,
            'cssPath' => $cssPath ?? null,
        ]);
    }
}