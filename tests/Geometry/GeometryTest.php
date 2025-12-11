<?php
declare(strict_types=1);

use GeoJson\GeoJson;
use GeoJson\Geometry\Geometry;

test('is subclass of geo json')
    ->expect(is_subclass_of(Geometry::class, GeoJson::class))
    ->toBeTrue();