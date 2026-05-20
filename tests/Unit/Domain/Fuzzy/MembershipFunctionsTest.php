<?php

use App\Domain\Fuzzy\MembershipFunctions;

/*
|--------------------------------------------------------------------------
| Linear turun
|--------------------------------------------------------------------------
*/

it('linearTurun returns 1 when x is at or below a', function () {
    expect(MembershipFunctions::linearTurun(3.10, 3.25, 3.6))->toBe(1.0);
    expect(MembershipFunctions::linearTurun(3.25, 3.25, 3.6))->toBe(1.0);
});

it('linearTurun returns 0 when x is at or above b', function () {
    expect(MembershipFunctions::linearTurun(3.6, 3.25, 3.6))->toBe(0.0);
    expect(MembershipFunctions::linearTurun(4.0, 3.25, 3.6))->toBe(0.0);
});

it('linearTurun interpolates linearly between a and b', function () {
    $result = MembershipFunctions::linearTurun(3.4, 3.25, 3.6);
    expect($result)->toBeFloat();
    expect(abs($result - ((3.6 - 3.4) / (3.6 - 3.25))))->toBeLessThanOrEqual(0.01);
});

it('linearTurun rejects invalid params (a >= b)', function () {
    expect(fn () => MembershipFunctions::linearTurun(3.5, 3.6, 3.6))
        ->toThrow(InvalidArgumentException::class);
});

/*
|--------------------------------------------------------------------------
| Segitiga
|--------------------------------------------------------------------------
*/

it('segitiga returns 1 at the peak (x = b)', function () {
    expect(MembershipFunctions::segitiga(3.5, 3.25, 3.5, 3.75))->toBe(1.0);
});

it('segitiga returns 0 when x is at or outside [a, c]', function () {
    expect(MembershipFunctions::segitiga(3.25, 3.25, 3.5, 3.75))->toBe(0.0);
    expect(MembershipFunctions::segitiga(3.0, 3.25, 3.5, 3.75))->toBe(0.0);
    expect(MembershipFunctions::segitiga(3.75, 3.25, 3.5, 3.75))->toBe(0.0);
    expect(MembershipFunctions::segitiga(4.0, 3.25, 3.5, 3.75))->toBe(0.0);
});

it('segitiga rises linearly from a to b', function () {
    $result = MembershipFunctions::segitiga(3.40, 3.25, 3.5, 3.75);
    expect(abs($result - ((3.40 - 3.25) / (3.5 - 3.25))))->toBeLessThanOrEqual(0.01);
});

it('segitiga falls linearly from b to c', function () {
    $result = MembershipFunctions::segitiga(3.65, 3.25, 3.5, 3.75);
    expect(abs($result - ((3.75 - 3.65) / (3.75 - 3.5))))->toBeLessThanOrEqual(0.01);
});

it('segitiga rejects invalid params (need a < b < c)', function () {
    expect(fn () => MembershipFunctions::segitiga(3.5, 3.5, 3.5, 3.5))
        ->toThrow(InvalidArgumentException::class);
});

/*
|--------------------------------------------------------------------------
| Linear naik
|--------------------------------------------------------------------------
*/

it('linearNaik returns 0 when x is at or below a', function () {
    expect(MembershipFunctions::linearNaik(3.5, 3.6, 3.75))->toBe(0.0);
    expect(MembershipFunctions::linearNaik(3.6, 3.6, 3.75))->toBe(0.0);
});

it('linearNaik returns 1 when x is at or above b', function () {
    expect(MembershipFunctions::linearNaik(3.85, 3.6, 3.75))->toBe(1.0);
    expect(MembershipFunctions::linearNaik(3.75, 3.6, 3.75))->toBe(1.0);
});

it('linearNaik interpolates linearly between a and b', function () {
    $result = MembershipFunctions::linearNaik(3.65, 3.6, 3.75);
    expect(abs($result - ((3.65 - 3.6) / (3.75 - 3.6))))->toBeLessThanOrEqual(0.01);
});

it('linearNaik rejects invalid params (a >= b)', function () {
    expect(fn () => MembershipFunctions::linearNaik(3.5, 3.75, 3.6))
        ->toThrow(InvalidArgumentException::class);
});
