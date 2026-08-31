<?php

/*
 * Module front office routes are registered with a prefix read from the page
 * they are linked to, see src/routes/tags.php. That makes the route table a
 * function of the database, so it must not be frozen into a cached route file:
 * pages would keep their old URLs until someone rebuilt it by hand.
 */
test('the route table is not cached', function (): void {
    expect(app()->routesAreCached())->toBeFalse();
});
