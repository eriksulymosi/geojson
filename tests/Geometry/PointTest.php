<?php

declare(strict_types=1);

use GeoJson\Exception\InvalidArgumentException;
use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\Point;

test('is subclass of geometry')
    ->expect(is_subclass_of(Point::class, Geometry::class))
    ->toBeTrue();

test('constructor should require at least two elements in position')
    ->throws(InvalidArgumentException::class, 'Position requires at least two elements')
    ->expect(fn () => new Point([1]));

test('constructor should require integer or float elements in position')
    ->with([
        'strings' => ['1.0', '2'],
        'objects' => [new stdClass(), new stdClass()],
        'arrays' => [[], []],
    ])
    ->throws(InvalidArgumentException::class, 'Position elements must be integers or floats')
    ->expect(fn () => new Point(func_get_args()));

test('constructor should allow more than two elements in aposition')
    ->expect(new Point([1, 2, 3, 4])->getCoordinates())
    ->toEqual([1, 2, 3, 4]);

test('serialization', function (): void {
    $coordinates = [1, 1];
    $point = new Point($coordinates);

    $expected = [
        'type' => GeoJsonType::POINT->value,
        'coordinates' => $coordinates,
    ];

    expect(GeoJsonType::from($point->getType()))->toBe(GeoJsonType::POINT);
    expect($point->getCoordinates())->toBe($coordinates);
    expect($point->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc): void {
    $json = <<<'JSON'
        {
            "type": "Point",
            "coordinates": [1, 1]
        }
        JSON;

    $json = json_decode($json, $assoc);

    /** @var Point */
    $point = GeoJson::jsonUnserialize($json);

    expect($point)->toBeInstanceOf(Point::class);
    expect(GeoJsonType::from($point->getType()))->toBe(GeoJsonType::POINT);
    expect($point->getCoordinates())->toBe([1, 1]);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');
