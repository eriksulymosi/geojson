<?php

declare(strict_types=1);

use GeoJson\BoundingBox;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\GeometryCollection;
use GeoJson\Geometry\LinearRing;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;

test(
    'constructor should scan extra arguments for bounding box',
    function (callable $createSubjectWithExtraArguments): void {
        $box = mock(BoundingBox::class);

        $sut = $createSubjectWithExtraArguments();
        expect($sut->getBoundingBox())->toBeNull();

        $sut = $createSubjectWithExtraArguments($box);
        expect($sut->getBoundingBox())->toBe($box);
    }
)
    ->with('createSubjectWithExtraArguments');

test('serialization with bounding box', function (callable $createSubjectWithExtraArguments): void {
    $box = mock(BoundingBox::class);
    $box->shouldReceive('jsonSerialize')
        ->andReturn(['boundingBox']);

    $sut = $createSubjectWithExtraArguments($box);

    $json = $sut->jsonSerialize();

    expect($json)->toHaveKey('bbox');
    expect($json['bbox'])->toBe(['boundingBox']);
})
    ->with('createSubjectWithExtraArguments');

dataset(
    'createSubjectWithExtraArguments',
    [
        'feature_collection' => [fn (...$extraArgs): FeatureCollection => new FeatureCollection([], ...$extraArgs)],
        'feature' => [fn (...$extraArgs): Feature => new Feature(null, null, null, ...$extraArgs)],
        'geometry_collection' => [fn (...$extraArgs): GeometryCollection => new GeometryCollection([], ...$extraArgs)],
        'line_ring' => [fn (...$extraArgs): LinearRing => new LinearRing([[1, 1], [2, 2], [3, 3], [1, 1]], ...$extraArgs)],
        'line_string' => [fn (...$extraArgs): LineString => new LineString([[1, 1], [2, 2]], ...$extraArgs)],
        'multi_line_string' => [fn (...$extraArgs): MultiLineString => new MultiLineString([], ...$extraArgs)],
        'multi_point' => [fn (...$extraArgs): MultiPoint => new MultiPoint([], ...$extraArgs)],
        'multi_polygon' => [fn (...$extraArgs): MultiPolygon => new MultiPolygon([], ...$extraArgs)],
        'point' => [fn (...$extraArgs): Point => new Point([1, 1], ...$extraArgs)],
        'polygon' => [fn (...$extraArgs): Polygon => new Polygon(
            [[[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]], [[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]]],
            ...$extraArgs
        )],
    ]
);
