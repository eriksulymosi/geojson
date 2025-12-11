<?php
declare(strict_types=1);

use GeoJson\BoundingBox;
use GeoJson\CoordinateReferenceSystem\CoordinateReferenceSystem;
use GeoJson\Feature\Feature;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Geometry\GeometryCollection;
use GeoJson\Geometry\LineString;
use GeoJson\Geometry\LinearRing;
use GeoJson\Geometry\MultiLineString;
use GeoJson\Geometry\MultiPoint;
use GeoJson\Geometry\MultiPolygon;
use GeoJson\Geometry\Point;
use GeoJson\Geometry\Polygon;

test(
    'constructor should scan extra arguments for crs and bounding box',
    function (callable $createSubjectWithExtraArguments) {
        $box = mock(BoundingBox::class);
        $crs = mock(CoordinateReferenceSystem::class);

        $sut = $createSubjectWithExtraArguments();
        expect($sut->getBoundingBox())->toBeNull();
        expect($sut->getCrs())->toBeNull();

        $sut = $createSubjectWithExtraArguments($box);
        expect($sut->getBoundingBox())->toBe($box);
        expect($sut->getCrs())->toBeNull();

        $sut = $createSubjectWithExtraArguments($crs);
        expect($sut->getBoundingBox())->toBeNull();
        expect($sut->getCrs())->toBe($crs);

        $sut = $createSubjectWithExtraArguments($box, $crs);
        expect($sut->getBoundingBox())->toBe($box);
        expect($sut->getCrs())->toBe($crs);

        $sut = $createSubjectWithExtraArguments($crs, $box);
        expect($sut->getBoundingBox())->toBe($box);
        expect($sut->getCrs())->toBe($crs);

        // Not that you would, but you could…
        $sut = $createSubjectWithExtraArguments(null, null, $box, $crs);
        expect($sut->getBoundingBox())->toBe($box);
        expect($sut->getCrs())->toBe($crs);
    }
)
    ->with('createSubjectWithExtraArguments');

test('serialization with crs and bounding box', function (callable $createSubjectWithExtraArguments) {
    $box = mock(BoundingBox::class);
    $box->shouldReceive('jsonSerialize')
        ->andReturn(['boundingBox']);

    $crs = mock(CoordinateReferenceSystem::class);
    $crs->shouldReceive('jsonSerialize')
        ->andReturn(['coordinateReferenceSystem']);
    
    $sut = $createSubjectWithExtraArguments($box, $crs);

    $json = $sut->jsonSerialize();

    expect($json)->toHaveKey('bbox');
    expect($json)->toHaveKey('crs');
    expect($json['bbox'])->toBe(['boundingBox']);
    expect($json['crs'])->toBe(['coordinateReferenceSystem']);
})
    ->with('createSubjectWithExtraArguments');

dataset(
    'createSubjectWithExtraArguments',
    [
        'feature_collection' => [fn(...$extraArgs): FeatureCollection => new FeatureCollection([], ... $extraArgs)],
        'feature' => [fn(...$extraArgs): Feature => new Feature(null, null, null, ...$extraArgs)],
        'geometry_collection' => [fn(...$extraArgs): GeometryCollection => new GeometryCollection([], ...$extraArgs)],
        'line_ring' => [fn(...$extraArgs): LinearRing => new LinearRing([[1, 1], [2, 2], [3, 3], [1, 1]], ...$extraArgs)],
        'line_string' => [fn(...$extraArgs): LineString => new LineString([[1, 1], [2, 2]], ...$extraArgs)],
        'multi_line_string' => [fn(...$extraArgs): MultiLineString => new MultiLineString([], ...$extraArgs)],
        'multi_point' => [fn(...$extraArgs): MultiPoint => new MultiPoint([], ...$extraArgs)],
        'multi_polygon' => [fn(...$extraArgs): MultiPolygon => new MultiPolygon([], ...$extraArgs)],
        'point' => [fn(...$extraArgs): Point => new Point([1, 1], ...$extraArgs)],
        'polygon' => [fn(...$extraArgs): Polygon => new Polygon(
            [[[0, 0], [0, 4], [4, 4], [4, 0], [0, 0]], [[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]]],
            ...$extraArgs
        )]
    ]
);