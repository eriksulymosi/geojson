<?php
declare(strict_types=1);

use GeoJson\BoundingBox;
use GeoJson\CoordinateReferenceSystem\Named;
use GeoJson\Exception\UnserializationException;
use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Point;
use GeoJson\JsonUnserializable;

test('is json serializable', function () {
    expect($this->createMock(GeoJson::class))->toBeInstanceOf(JsonSerializable::class);
});

test('is json unserializable', function () {
    expect($this->createMock(GeoJson::class))->toBeInstanceOf(JsonUnserializable::class);
});

test('unserialization with bounding box', function ($assoc) {
    $json = <<<'JSON'
        {
            "type": "Point",
            "coordinates": [1, 1],
            "bbox": [-180.0, -90.0, 180.0, 90.0]
        }
    JSON;

    $json = json_decode($json, $assoc);
    /** @var Point */
    $point = GeoJson::jsonUnserialize($json);

    expect($point)->toBeInstanceOf(Point::class);
    expect(GeoJsonType::from($point->getType()))->toBe(GeoJsonType::POINT);
    expect($point->getCoordinates())->toBe([1, 1]);

    $boundingBox = $point->getBoundingBox();

    expect($boundingBox)->toBeInstanceOf(BoundingBox::class);
    expect($boundingBox->getBounds())->toBe([-180.0, -90.0, 180.0, 90.0]);
})
    ->with('provideJsonDecodeAssocOptions')
    ->group('functional');

test('unserialization with crs', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "Point",
        "coordinates": [1, 1],
        "crs": {
            "type": "name",
            "properties": {
                "name": "urn:ogc:def:crs:OGC:1.3:CRS84"
            }
        }
    }
    JSON;

    $json = json_decode($json, $assoc);
    /** @var Point */
    $point = GeoJson::jsonUnserialize($json);

    expect($point)->toBeInstanceOf(Point::class);
    expect(GeoJsonType::from($point->getType()))->toBe(GeoJsonType::POINT);
    expect($point->getCoordinates())->toBe([1, 1]);

    $crs = $point->getCrs();

    $expectedProperties = ['name' => 'urn:ogc:def:crs:OGC:1.3:CRS84'];

    expect($crs)->toBeInstanceOf(Named::class);
    expect($crs->getType())->toBe('name');
    expect($crs->getProperties())->toBe($expectedProperties);
})
    ->with('provideJsonDecodeAssocOptions')->group('functional');

test('unserialization with unknown type', function () {
    GeoJson::jsonUnserialize(['type' => 'Unknown']);
})
    ->throws(ValueError::class);

test('unserialization with missing type', function () {
    // expect()->toThrow(ValueError::class, '"Unknown" is not a valid backing value for enum GeoJson\GeoJsonType');
    $this->expectException(UnserializationException::class);
    $this->expectExceptionMessage('GeoJson expected "type" property of type string, none given');

    GeoJson::jsonUnserialize([]);
});

// test('unserialization with missing coordinates', function (string $type) {
//     $this->expectException(UnserializationException::class);
//     $this->expectExceptionMessage($type . ' expected "coordinates" property of type array, none given');

//     GeoJson::jsonUnserialize([
//         'type' => $type,
//     ]);
// })
//     ->with([
//         GeoJsonType::LINE_STRING => [GeoJsonType::LINE_STRING],
//         GeoJsonType::MULTI_LINE_STRING => [GeoJsonType::MULTI_LINE_STRING],
//         GeoJsonType::MULTI_POINT => [GeoJsonType::MULTI_POINT],
//         GeoJsonType::MULTI_POLYGON => [GeoJsonType::MULTI_POLYGON],
//         GeoJsonType::POINT => [GeoJsonType::POINT],
//         GeoJsonType::POLYGON => [GeoJsonType::POLYGON],
//     ]);
    
test('unserialization with invalid coordinates', function ($value) {
    $valueType = is_object($value) ? get_class($value) : gettype($value);

    $this->expectException(UnserializationException::class);
    $this->expectExceptionMessage('Point expected "coordinates" property of type array, ' . $valueType . ' given');

    GeoJson::jsonUnserialize([
        'type' => GeoJsonType::POINT->value,
        'coordinates' => $value,
    ]);
})
    ->with([
        'string' => ['1,1'],
        'int' => [1],
        'bool' => [false],
    ]);

test('feature unserialization with invalid geometry', function () {
    $this->expectException(UnserializationException::class);
    $this->expectExceptionMessage('Feature expected "geometry" property of type array or object, string given');

    GeoJson::jsonUnserialize([
        'type' => GeoJsonType::FEATURE->value,
        'geometry' => 'must be array or object, but this is a string',
    ]);
});

test('feature unserialization with invalid properties', function () {
    $this->expectException(UnserializationException::class);
    $this->expectExceptionMessage('Feature expected "properties" property of type array or object, string given');

    GeoJson::jsonUnserialize([
        'type' => GeoJsonType::FEATURE->value,
        'properties' => 'must be array or object, but this is a string',
    ]);
});

dataset('provideJsonDecodeAssocOptions', fn(): array => [
    'assoc=true' => [true],
    'assoc=false' => [false],
]);

