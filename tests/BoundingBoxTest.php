<?php

declare(strict_types=1);

use GeoJson\BoundingBox;
use GeoJson\Exception\InvalidArgumentException;
use GeoJson\JsonUnserializable;

test('is json serializable')
    ->expect(new BoundingBox([0, 0, 1, 1]))
    ->toBeInstanceOf('JsonSerializable');

test('is json unserializable')
    ->expect(new BoundingBox([0, 0, 1, 1]))
    ->toBeInstanceOf(JsonUnserializable::class);

test('constructor should require at least four values')
    ->throws(InvalidArgumentException::class, 'BoundingBox requires at least four values')
    ->expect(fn () => new BoundingBox([0, 0]));

test('constructor should require an even number of values')
    ->throws(InvalidArgumentException::class, 'BoundingBox requires an even number of values')
    ->expect(fn () => new BoundingBox([0, 0, 1, 1, 2]));

test('constructor should require integer or float values')
    ->with([
        'strings' => ['0', '0.0', '1', '1.0'],
        'objects' => [new stdClass(), new stdClass(), new stdClass(), new stdClass()],
        'arrays' => [[], [], [], []],
    ])
    ->throws(InvalidArgumentException::class, 'BoundingBox values must be integers or floats')
    ->expect(fn (...$bounds) => new BoundingBox($bounds));

test('constructor should require min before max values')
    ->throws(InvalidArgumentException::class, 'BoundingBox min values must precede max values')
    ->expect(fn () => new BoundingBox([-90.0, -95.0, -92.5, 90.0]));

test('serialization', function (): void {
    $bounds = [-180.0, -90.0, 0.0, 180.0, 90.0, 100.0];
    $boundingBox = new BoundingBox($bounds);

    expect($boundingBox->getBounds())->toBe($bounds);
    expect($boundingBox->jsonSerialize())->toBe($bounds);
});

test('unserialization', function ($assoc): void {
    $json = '[-180.0, -90.0, 180.0, 90.0]';

    $json = json_decode($json, $assoc);
    $boundingBox = BoundingBox::jsonUnserialize($json);

    expect($boundingBox)->toBeInstanceOf(BoundingBox::class);
    expect($boundingBox->getBounds())->toBe([-180.0, -90.0, 180.0, 90.0]);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');

test('unserialization should require array')
    ->with([
        null,
        1,
        'foo',
        new stdClass(),
    ])
    ->throws(TypeError::class)
    ->expect(fn ($json) => BoundingBox::jsonUnserialize($json));
