<?php
declare(strict_types=1);

use GeoJson\BoundingBox;
use GeoJson\Exception\InvalidArgumentException;
use GeoJson\Exception\UnserializationException;
use GeoJson\JsonUnserializable;

test('is json serializable', function () {
    expect(new BoundingBox([0, 0, 1, 1]))->toBeInstanceOf('JsonSerializable');
});

test('is json unserializable', function () {
    expect(new BoundingBox([0, 0, 1, 1]))->toBeInstanceOf(JsonUnserializable::class);
});

test('constructor should require at least four values', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('BoundingBox requires at least four values');

    new BoundingBox([0, 0]);
});

test('constructor should require an even number of values', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('BoundingBox requires an even number of values');

    new BoundingBox([0, 0, 1, 1, 2]);
});

test('constructor should require integer or float values', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('BoundingBox values must be integers or floats');
    new BoundingBox(func_get_args());
})
    ->with([
        'strings' => ['0', '0.0', '1', '1.0'],
        'objects' => [new stdClass(), new stdClass(), new stdClass(), new stdClass()],
        'arrays' => [[], [], [], []],
    ]);

test('constructor should require min before max values', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('BoundingBox min values must precede max values');

    new BoundingBox([-90.0, -95.0, -92.5, 90.0]);
});

test('serialization', function () {
    $bounds = [-180.0, -90.0, 0.0, 180.0, 90.0, 100.0];
    $boundingBox = new BoundingBox($bounds);

    expect($boundingBox->getBounds())->toBe($bounds);
    expect($boundingBox->jsonSerialize())->toBe($bounds);
});

test('unserialization', function ($assoc) {
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

test('unserialization should require array', function ($value) {
    $this->expectException(UnserializationException::class);
    $this->expectExceptionMessage('BoundingBox expected value of type array');

    BoundingBox::jsonUnserialize($value);
})
    ->with([
        [null],
        [1],
        ['foo'],
        [new stdClass()],
    ]);

