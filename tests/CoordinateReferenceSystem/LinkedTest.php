<?php
declare(strict_types=1);

use GeoJson\CoordinateReferenceSystem\CoordinateReferenceSystem;
use GeoJson\CoordinateReferenceSystem\Linked;
use GeoJson\Exception\UnserializationException;

test('is subclass of coordinate reference system')
    ->expect(is_subclass_of(Linked::class, CoordinateReferenceSystem::class))
    ->toBeTrue();

test('serialization', function () {
    $crs = new Linked('https://example.com/crs/42', 'proj4');

    $expected = [
        'type' => 'link',
        'properties' => [
            'href' => 'https://example.com/crs/42',
            'type' => 'proj4',
        ],
    ];

    expect($crs->getType())->toBe('link');
    expect($crs->getProperties())->toBe($expected['properties']);
    expect($crs->jsonSerialize())->toBe($expected);
});

test('serialization without href type', function () {
    $crs = new Linked('https://example.com/crs/42');

    $expected = [
        'type' => 'link',
        'properties' => [
            'href' => 'https://example.com/crs/42',
        ],
    ];

    expect($crs->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "link",
        "properties": {
            "href": "https://example.com/crs/42",
            "type": "proj4"
        }
    }
    JSON;

    $json = json_decode($json, $assoc);
    $crs = CoordinateReferenceSystem::jsonUnserialize($json);

    $expectedProperties = [
        'href' => 'https://example.com/crs/42',
        'type' => 'proj4',
    ];

    expect($crs)->toBeInstanceOf(Linked::class);
    expect($crs->getType())->toBe('link');
    expect($crs->getProperties())->toBe($expectedProperties);
})
    ->with('provideJsonDecodeAssocOptions')
    ->group('functional');

test('unserialization without href type', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "link",
        "properties": {
            "href": "https://example.com/crs/42"
        }
    }
    JSON;

    $json = json_decode($json, $assoc);
    $crs = CoordinateReferenceSystem::jsonUnserialize($json);

    $expectedProperties = ['href' => 'https://example.com/crs/42'];

    expect($crs)->toBeInstanceOf(Linked::class);
    expect($crs->getType())->toBe('link');
    expect($crs->getProperties())->toBe($expectedProperties);
})
    ->with('provideJsonDecodeAssocOptions')
    ->group('functional');

test('unserialization should require properties array or object', function () {
    CoordinateReferenceSystem::jsonUnserialize(['type' => 'link', 'properties' => null]);
})
    ->throws(TypeError::class);

test('unserialization should require href property', function () {
    $this->expectException(UnserializationException::class);
    $this->expectExceptionMessage('Linked CRS expected "properties.href" property of type string');

    CoordinateReferenceSystem::jsonUnserialize(['type' => 'link', 'properties' => []]);
});

dataset('provideJsonDecodeAssocOptions', function () {
    return [
        'assoc=true' => [true],
        'assoc=false' => [false],
    ];
});
