## What is?
A package that allow overlapping (virtually) infinite slideovers simultaneously with configurable widths. </br>
This package is based upon aristridely/slideover (thanks [Aristotele Tufano] (https://github.com/ariStridely)) which is a fork of wire-elements/modal (thanks [Philo Hermans](https://github.com/philoNL)).

## Installation
To get started, require the package via Composer:
```
composer require cloudmeshdev/livewire-slideover
```

## Livewire directive
Add the Livewire directive `@livewire('livewire-ui-slideover')` to your template.
```html
<html>
<body>
    <!-- content -->

    @livewire('livewire-ui-slideover')
</body>
</html>
```

## Alpine
[Alpine](https://github.com/alpinejs/alpine) is already combined with Livewire v3

## TailwindCSS
The base slideover template is made with TailwindCSS. If you use a different CSS framework I recommend that you publish the slideover template and change the markup to include the required classes for your CSS framework.
```shell
php artisan vendor:publish --tag=livewire-ui-slideover-views
```

## Creating a slideover
You can run `php artisan make:livewire EditUserSlideover` to make the initial Livewire component. Open your component class and make sure it extends the `SlideoverComponent` class:

```php
<?php

namespace App\Http\Livewire;

use LivewireUI\Slideover\SlideoverComponent;

class EditUserSlideover extends SlideoverComponent
{
    public function render()
    {
        return view('livewire.edit-user-slideover');
    }
}
```

## Opening a slideover
To open a slideover you will need to dispatch an event. To open the `EditUserSlideover` slideover for example:

```html
<!-- Outside of any Livewire component -->
<button onclick="Livewire.dispatch('openSlideover', {component: 'edit-user-slideover'})">Edit User</button>

<!-- Inside existing Livewire component -->
<button wire:click="$dispatch('openSlideover', {component: 'edit-user-slideover'})">Edit User</button>

<!-- Taking namespace into account for component Admin/EditUser -->
<button wire:click="$dispatch('openSlideover', {component: 'admin.edit-user-slideover'})">Edit User</button>
```

## Passing parameters
To open the `EditUserSlideover` slideover for a specific user we can pass the user id (notice the single quotes):

```html
<!-- Outside of any Livewire component -->
<button onclick="Livewire.dispatch(
    'openSlideover', { 
        component: 'edit-user-slideover', 
        arguments: { 
            user: {{ $user->id }}
        },
        slideoverAttributes: {
            closeOnClickAway: false
        }
    })"
>Edit User</button>

<!-- Inside existing Livewire component -->
<button wire:click="$dispatch(
    'openSlideover', { 
        component: 'edit-user-slideover', 
        arguments: { 
            user: {{ $user->id }}
        }
    })"
>Edit User</button>

<!-- If you use a different primaryKey (e.g. email), adjust accordingly -->
<button wire:click="$dispatch(
    'openSlideover', { 
        component: 'edit-user-slideover', 
        arguments: { 
            user: {{ $user->email }}
        }
    })"
>Edit User</button>

<!-- Example of passing multiple parameters -->
<button wire:click="$dispatch(
    'openSlideover', { 
        component: 'edit-user-slideover', 
        arguments: { 
            user: {{ $user->email }},
            isAdmin: {{ $isAdmin }}
        }
    })"
>Edit User</button>
```

The parameters are passed to the `mount` method on the slideover component:

```php
<?php

namespace App\Http\Livewire;

use App\Models\User;
use LivewireUI\Slideover\SlideoverComponent;

class EditUserSlideover extends SlideoverComponent
{
    public User $user;

    public function mount(User $user)
    {
        Gate::authorize('update', $user);

        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.edit-user-slideover');
    }
}
```

## Overlapping slideover
From an existing slideover you can use the exact same event and another slideover will stack onto the first:

```html
<!-- Edit User Slideover -->

<!-- Edit Form -->

<button wire:click="$dispatch(
    'openSlideover', { 
        component: 'delete-user-slideover', 
        arguments: { 
            user: {{ $user->id }}
        }
    })"
>Delete User</button>
```

## Closing a slideover
When you are done with the current slideover, you can close it by dispatching the `closeSlideover` event. 
This will always close the most recent (in the foreground) slideover. </br>
(N.B. at the moment, each time you close a slideover, the relative component's state will be destroyed).
```html
<button wire:click="$dispatch('closeSlideover')">Close</button>
```

You can also close a slideover from within your slideover component class:

```php
<?php

namespace App\Http\Livewire;

use App\Models\User;
use LivewireUI\Slideover\SlideoverComponent;

class EditUserSlideover extends SlideoverComponent
{
    public User $user;

    public function mount(User $user)
    {
        Gate::authorize('update', $user);

        $this->user = $user;
    }

    public function update()
    {
        Gate::authorize('update', $user);

        $this->user->update($data);

        $this->closeSlideover();
    }

    public function render()
    {
        return view('livewire.edit-user-slideover');
    }
}
```

## Configure slideover width
You can change the width of the slideover in 2 ways. </br>
(N.B. At the moment the width of the slideover is configurable by a CSS class, in this case a TailwindCSS one).
### Static way
Declare this method inside your class. </br>
Usefull when you know the slideover will always have the same width.
```php
public static function slideoverWidth(): string
{
    return 'w-11/12';
}
```

### Dynamic way
This approach is usefull when the slideover is opened on top of another slideover and you want to let the background one visible adapting the width of the new slidevoer in the foreground.
```html
{{-- Blade View --}}
@php
    $slideoverData = json_encode(['user' => $user->id]);
    $slideoverAttributes = json_encode(['width' => 'w-8/12']);
@endphp

<button wire:click="$dispatch(
    'openSlideover', { 
        component: 'delete-user-slideover', 
        arguments: {{ $slideoverData }},
        slideoverAttributes: {{ $slideoverAttributes }}
    })"
>Delete User</button>
```

## Difference with aristridely/slideover
- re-enabled closing slideover with `escape` key
- re-enabled closing slideover by clicking outside of it (close on click away)

## Combine with wire-elements/modal
This package can be used together with `wire-elements/modal`. The recommended way is to declare it before the modal package, as follow:

```html
<html>
<body>
    <!-- content -->

    @livewire('livewire-ui-slideover')
    @livewire('livewire-ui-modal')
</body>
</html>
```
This will ensure that the modal element will always be on top of the slideover one.

Warning: 
Closing on escape and on clickaway will collide with the modal behavior. </br>
E.g. If a modal is opened on top of a slideover, by pressing `escape` both will be closed. </br>

## Configuration
You can (partially) customize the Slideover via the `livewire-ui-slideover.php` config file. </br>
(Actually only default width class).

 To publish the config run the vendor:publish command:
```shell
php artisan vendor:publish --tag=livewire-ui-slideover-config
```
