<?php
declare(strict_types=1);

use GeoJson\CoordinateReferenceSystem\CoordinateReferenceSystem;
use GeoJson\CoordinateReferenceSystem\Named;
use GeoJson\Exception\UnserializationException;

test('is subclass of coordinate reference system')
    ->expect(is_subclass_of(Named::class, CoordinateReferenceSystem::class))
    ->toBeTrue();

test('serialization', function () {
    $crs = new Named('urn:ogc:def:crs:OGC:1.3:CRS84');

    $expected = [
        'type' => 'name',
        'properties' => [
            'name' => 'urn:ogc:def:crs:OGC:1.3:CRS84'
        ],
    ];

    expect($crs->getType())->toBe('name');
    expect($crs->getProperties())->toBe($expected['properties']);
    expect($crs->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "name",
        "properties": {
            "name": "urn:ogc:def:crs:OGC:1.3:CRS84"
        }
    }
    JSON;

    $json = json_decode($json, $assoc);
    $crs = CoordinateReferenceSystem::jsonUnserialize($json);

    $expectedProperties = ['name' => 'urn:ogc:def:crs:OGC:1.3:CRS84'];

    expect($crs)->toBeInstanceOf(Named::class);
    expect($crs->getType())->toBe('name');
    expect($crs->getProperties())->toBe($expectedProperties);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');

test('unserialization should require properties array or object', function () {
    CoordinateReferenceSystem::jsonUnserialize(['type' => 'name', 'properties' => null]);
})
    ->throws(TypeError::class);

test('unserialization should require name property', function () {
    $this->expectException(UnserializationException::class);
    $this->expectExceptionMessage('Named CRS expected "properties.name" property of type string');

    CoordinateReferenceSystem::jsonUnserialize(['type' => 'name', 'properties' => []]);
});


