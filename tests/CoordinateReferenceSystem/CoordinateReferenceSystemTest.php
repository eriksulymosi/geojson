<?php
declare(strict_types=1);

use GeoJson\CoordinateReferenceSystem\CoordinateReferenceSystem;
use GeoJson\Exception\UnserializationException;
use GeoJson\JsonUnserializable;

test('is json serializable')
    ->expect(mock(CoordinateReferenceSystem::class))
    ->toBeInstanceOf(JsonSerializable::class);

test('is json unserializable')
    ->expect(mock(CoordinateReferenceSystem::class))
    ->toBeInstanceOf(JsonUnserializable::class);

test('unserialization should require type field', function () {
    CoordinateReferenceSystem::jsonUnserialize(['properties' => []]);
})
    ->throws(UnserializationException::class, 'CRS expected "type" property of type string, none given');

test('unserialization should require properties field', function () {
    CoordinateReferenceSystem::jsonUnserialize(['type' => 'foo']);
})
    ->throws(UnserializationException::class, 'CRS expected "properties" property of type array or object, none given');

test('unserialization should require valid type', function () {
    CoordinateReferenceSystem::jsonUnserialize(['type' => 'foo', 'properties' => []]);
})
    ->throws(UnserializationException::class, 'Invalid CRS type "foo"');
