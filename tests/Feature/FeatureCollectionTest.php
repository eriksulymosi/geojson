<?php
declare(strict_types=1);

use GeoJson\Exception\InvalidArgumentException;
use GeoJson\Exception\UnserializationException;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\GeoJson;
use GeoJson\GeoJsonType;
use GeoJson\Geometry\Point;

test('is subclass of geo json')
    ->expect(is_subclass_of(FeatureCollection::class, GeoJson::class))
    ->toBeTrue();

test('constructor should require array of feature objects', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('FeatureCollection may only contain Feature objects');

    new FeatureCollection([new stdClass()]);
});

test('constructor should reindex features array numerically', function () {
    $feature1 = mock(Feature::class);
    $feature2 = mock(Feature::class);

    $features = [
        'one' => $feature1,
        'two' => $feature2,
    ];

    $collection = new FeatureCollection($features);

    expect(iterator_to_array($collection))->toBe([$feature1, $feature2]);
});

test('is traversable', function () {
    $features = [
        mock(Feature::class),
        mock(Feature::class),
    ];

    $collection = new FeatureCollection($features);

    expect($collection)->toBeInstanceOf('Traversable');
    expect(iterator_to_array($collection))->toBe($features);
});

test('is countable', function () {
    $features = [
        mock(Feature::class),
        mock(Feature::class),
    ];

    $collection = new FeatureCollection($features);

    expect($collection)->toBeInstanceOf('Countable');
    expect($collection)->toHaveCount(2);
});

test('serialization', function () {
    $features = [
        mock(Feature::class),
        mock(Feature::class),
    ];

    $features[0]->shouldReceive('jsonSerialize')->andReturn(['feature1']);
    $features[1]->shouldReceive('jsonSerialize')->andReturn(['feature2']);

    $collection = new FeatureCollection($features);

    $expected = [
        'type' => GeoJsonType::FEATURE_COLLECTION->value,
        'features' => [['feature1'], ['feature2']],
    ];

    expect(GeoJsonType::from($collection->getType()))->toBe(GeoJsonType::FEATURE_COLLECTION);
    expect($collection->getFeatures())->toBe($features);
    expect($collection->jsonSerialize())->toBe($expected);
});

test('unserialization', function ($assoc) {
    $json = <<<'JSON'
    {
        "type": "FeatureCollection",
        "features": [
            {
                "type": "Feature",
                "id": "test.feature.1",
                "geometry": {
                    "type": "Point",
                    "coordinates": [1, 1]
                }
            }
        ]
    }
    JSON;

    $json = json_decode($json, $assoc);

    /** @var FeatureCollection */
    $collection = GeoJson::jsonUnserialize($json);

    expect($collection)->toBeInstanceOf(FeatureCollection::class);
    expect(GeoJsonType::from($collection->getType()))->toBe(GeoJsonType::FEATURE_COLLECTION);
    expect($collection)->toHaveCount(1);

    $features = iterator_to_array($collection);
    $feature = $features[0];

    expect($feature)->toBeInstanceOf(Feature::class);
    expect(GeoJsonType::from($feature->getType()))->toBe(GeoJsonType::FEATURE);
    expect($feature->getId())->toBe('test.feature.1');
    expect($feature->getProperties())->toBeNull();

    $geometry = $feature->getGeometry();

    expect($geometry)->toBeInstanceOf(Point::class);
    expect(GeoJsonType::from($geometry->getType()))->toBe(GeoJsonType::POINT);
    expect($geometry->getCoordinates())->toBe([1, 1]);
})
    ->with([
        'assoc=true' => [true],
        'assoc=false' => [false],
    ])
    ->group('functional');

test('unserialization should require features property', function () {
    $this->expectException(UnserializationException::class);
    $this->expectExceptionMessage('FeatureCollection expected "features" property of type array, none given');

    GeoJson::jsonUnserialize(['type' => GeoJsonType::FEATURE_COLLECTION->value]);
});

test('unserialization should require features array', function () {
    $this->expectException(UnserializationException::class);
    $this->expectExceptionMessage('FeatureCollection expected "features" property of type array');

    GeoJson::jsonUnserialize(['type' => GeoJsonType::FEATURE_COLLECTION->value, 'features' => null]);
});
