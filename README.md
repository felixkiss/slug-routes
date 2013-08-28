# What does it do?

If you are using the [Route Model Binding] feature in [Laravel 4], slug-routes gives 
you the ability to use some unique piece of data to represent your records in URLs 
other than the ID. This can help optimising your URLs for search engines, and generally
give your users a better chance to grasp the functionality of your web application.

# Installation

## Install the package

Install the package through [Composer].

In your `composer.json` file:

```json
{
    "require": {
        "laravel/framework": "4.0.*",
        "felixkiss/slug-routes": "dev-master"
    }
}
```

Run `composer update` to install the package.

## Configure Laravel

Add the following to your `providers` array in `config/app.php`:

```php
'providers' => array(
    // ...

    'Felixkiss\SlugRoutes\SlugRoutesServiceProvider',
),
```

# Usage

*In the following examples, I use `Closure` callback routes only. I use [Controller Routing]
all the time in my actual apps, but Closures just keep it simple and clean and I think thats
more suitable just to showcase the functionality of this package.*

## Route Model Binding

[Route Model Binding] is a really cool feature of Laravel. It gives you the ability
to specify a special parameter type for your routes to inject model instances into your
Routes. For example, instead of doing the standard thing of

```php
// Access via http://example.com/users/1
Route::get('users/{id}', function($id)
{
    $user = User::find($id);

    if(is_null($user))
        App::abort(404);

    return View::make('users.show', compact('user'));
});
```

you can define a route parameter `{user}` that takes an `id` and does the fetching of
the record for you. It will even raise an 404, if no user with the existing id exists:

```php
// Access via http://example.com/users/1
Route::model('user', 'User');
Route::get('users/{user}', function(User $user)
{
    return View::make('users.show', compact('user'));
});
```

This removes a lot of boilerplate code, since you can reuse this `{user}` in as many of
your routes as you want.

# The Problem

This is really convenient, but what if you want to have SEO/user-friendly URLs like
`/users/taylor-otwell` instead of `/users/1`? Basically, you have three options:

## Option 1 - Plain Old Route

```php
Route::get('users/{slug}', function($slug)
{
    $user = User::where('slug', $slug);

    if(is_null($user))
        App::abort(404);

    return View::make('users.show', compact('user'));
});
```

This is fine, but we wanted to get rid of the boilerplate. That was the reason
we started to use `Route::model()` in the first place.

## Option 2 - `Route::bind()`

```php
Route::bind('user', function($value, $route)
{
    return User::where('slug', $value)->first();
});
```

[The docs](http://laravel.com/docs/routing#route-model-binding) mention this as the
way to go, if we want custom URL to model matching. It works fine but I think the
`routes.php` file is not always the right place to define this kind of thing.

## Option 3 - Use `slug-routes`

This package provides `SluggableInterface`, which you can implement in your Model class.
If you do, `Route::model('user', 'User')` will recognize it and bind to the database column
returned by `getSlugIdentifier()` automatically. If you don't implement the interface
it simply falls back to the default behavior provided by `Route::model()`.

# Example

Model:
```php
<?php

use Felixkiss\SlugRoutes\SluggableInterface;

class User extends Eloquent implements SluggableInterface
{
    // ...

    /**
     * Use 'slug' db column as the identifier in URLs for this model.
     * Should be URL friendly and unique to avoid collisions.
     *
     * @return string
     */
    public function getSlugIdentifier()
    {
        return 'slug';
    }
}
```

`routes.php`:
```php
// Bind {user} to User model
// If model implements SluggableInterface, it will try to match
// the value to the column specified by getSlugIdentifier()
// If model doesn't implement SluggableInterface, it will fallback
// to the default behaviour of using id.
Route::model('user', 'User');

Route::get('users/{user}', array('as' => 'users.show', function()
{
    return View::make('users.show', compact('user'));
}));
```

A full example, including a User model with slug, UsersController, Views, can be found [on github](https://github.com/felixkiss/slug-routes-example).

# License

slug-routes is open source software, licensed under the [MIT License].

[Route Model Binding]: http://laravel.com/docs/routing#route-model-binding
[Controller Routing]: http://laravel.com/docs/routing#routing-to-controllers
[Laravel 4]: http://laravel.com/
[Composer]: http://getcomposer.org/
[MIT License]: http://opensource.org/licenses/MIT