<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('OFFSET', 268435456);
define('RADIUS', 85445659.4471); /* $offset / pi() */

function lonToX($lon) {
    return round(OFFSET + RADIUS * $lon * pi() / 180);
}

function latToY($lat) {
    return round(OFFSET - RADIUS *
            log((1 + sin($lat * pi() / 180)) /
                    (1 - sin($lat * pi() / 180))) / 2);
}

function pixelDistance($lat1, $lon1, $lat2, $lon2, $zoom) {
    $x1 = lonToX($lon1);
    $y1 = latToY($lat1);

    $x2 = lonToX($lon2);
    $y2 = latToY($lat2);

    return sqrt(pow(($x1 - $x2), 2) + pow(($y1 - $y2), 2)) >> (21 - $zoom);
}

function haversineDistance($lat1, $lon1, $lat2, $lon2) {
    $lat1 = doubleval($lat1);
    $lon1 = doubleval($lon1);
    $lat2 = doubleval($lat2);
    $lon2 = doubleval($lon2);
    $latd = deg2rad($lat2 - $lat1);
    $lond = deg2rad($lon2 - $lon1);
    $a = sin($latd / 2) * sin($latd / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lond / 2) * sin($lond / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return 6371.0 * $c;
}

function cluster($markers, $distance, $zoom) {
    $clustered = array();
    /* Loop until all markers have been compared. */
    while (count($markers)) {
        $marker = array_pop($markers);
        $cluster = array();
        /* Compare against all markers which are left. */
        foreach ($markers as $key => $target) {
            $pixels = haversineDistance($marker['tu_curlat'], $marker['tu_curlng'], $target['tu_curlat'], $target['tu_curlng'], $zoom);
            /* If two markers are closer than given distance remove */
            /* target marker from array and add it to cluster.      */
            if ($distance > $pixels) {
//                printf("Distance between %s,%s and %s,%s is %d pixels.\n", $marker['lat'], $marker['lon'], $target['lat'], $target['lon'], $pixels);
                unset($markers[$key]);
                $cluster[] = $target;
            }
        }

        /* If a marker has been added to cluster, add also the one  */
        /* we were comparing to and remove the original from array. */
        if (count($cluster) > 0) {
            $cluster[] = $marker;
            $clustered[] = $cluster;
        } else {
            $clustered[] = $marker;
        }
    }
    return $clustered;
}

function convexHull($points) {
    /* Ensure point doesn't rotate the incorrect direction as we process the hull halves */
    // ['0] => ['tu_curlat']
    // ['1] => ['tu_curlng']
    $cross = function($o, $a, $b) {
//        var_dump($a);
        return ($a['tu_curlat'] - $o['tu_curlat']) * ($b['tu_curlng'] - $o['tu_curlng']) - ($a['tu_curlng'] - $o['tu_curlng']) * ($b['tu_curlat'] - $o['tu_curlat']);
    };

    $pointCount = count($points);
    sort($points);
    if ($pointCount > 1) {

        $n = $pointCount;
        $k = 0;
        $h = array();

        /* Build lower portion of hull */
        for ($i = 0; $i < $n; ++$i) {
            while ($k >= 2 && $cross($h[$k - 2], $h[$k - 1], $points[$i]) <= 0)
                $k--;
            $h[$k++] = $points[$i];
        }

        /* Build upper portion of hull */
        for ($i = $n - 2, $t = $k + 1; $i >= 0; $i--) {
            while ($k >= $t && $cross($h[$k - 2], $h[$k - 1], $points[$i]) <= 0)
                $k--;
            $h[$k++] = $points[$i];
        }

        /* Remove all vertices after k as they are inside of the hull */
        if ($k > 1) {

            /* If you don't require a self closing polygon, change $k below to $k-1 */
            $h = array_splice($h, 0, $k);
        }

        return $h;
    } else if ($pointCount <= 1) {
        return $points;
    } else {
        return null;
    }
}
