<?php

namespace Kadegray\StatamicPhoneNumberFieldtype\Tests;

use Facades\Statamic\Fields\FieldtypeRepository as Fieldtype;
use Illuminate\Support\Facades\Route;
use Kadegray\StatamicPhoneNumberFieldtype\ServiceProvider;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Modifiers\Modify;

class ServiceProviderTest extends TestCase
{
    /**
     * If the ServiceProvider (routes, modifiers, fieldtype registration,
     * vite/publishables config) failed to boot, every other test in this
     * suite would already be failing — this is a standalone smoke test
     * making that failure mode explicit and easy to find.
     */
    #[Test]
    public function the_addon_boots_without_error()
    {
        $this->assertNotNull(Fieldtype::find('phone_number'));
    }

    #[Test]
    public function both_modifiers_are_registered_and_resolvable_by_handle()
    {
        $national = Modify::value('+12015550123')->e164_to_national()->fetch();
        $this->assertSame('(201) 555-0123', $national);

        $international = Modify::value('+12015550123')->e164_to_international()->fetch();
        $this->assertSame('+1 201-555-0123', $international);
    }

    #[Test]
    public function the_countries_action_route_is_registered()
    {
        $route = collect(Route::getRoutes())->first(
            fn ($route) => str_contains($route->uri(), 'statamic-phone-number-fieldtype') && str_contains($route->uri(), 'countries')
        );

        $this->assertNotNull($route, 'Expected the {lang}/countries action route to be registered.');
    }

    #[Test]
    public function images_and_js_are_registered_as_publishable()
    {
        $reflection = new \ReflectionClass(ServiceProvider::class);
        $publishables = $reflection->getDefaultProperties()['publishables'];

        $this->assertContains('images', $publishables);
        $this->assertContains('js', $publishables);
    }

    #[Test]
    public function vite_is_configured_with_the_addon_entrypoint()
    {
        $reflection = new \ReflectionClass(ServiceProvider::class);
        $vite = $reflection->getDefaultProperties()['vite'];

        $this->assertSame(['resources/js/addon.js'], $vite['input']);
        $this->assertSame('resources/dist', $vite['publicDirectory']);
    }
}
