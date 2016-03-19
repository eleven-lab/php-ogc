<?php

namespace LorenzoGiust\GeoSpatial;


use LorenzoGiust\GeoSpatial\Exceptions\GeoSpatialException;

class Point extends GeoSpatial
{
    protected static $greatCircleproviders = [ 'haversine', 'vincenty' ];

    public $lat;
	public $lon;
    public $address = null;


    /**
     * Point constructor.
     *
     * There are different ways to intantiate a Point Object:
     *
     * 1) from array containing a tuple of string|float lat, lon
     *      $point = new Point(["1.123", "2.345"])
     *      $point = new Point([1.123, 2.345])
     *
     * 2) from a string with a delimiters, by default "," is used
     *      $point = new Point("1.123, 2.345")
     *      $point = new Point("1.123: 2.345", [":"])
     *
     * 3) from two elements, strings or numerics
     *      $point = new Point(1.123, 2.345)
     *      $point = new Point("1.1232", "2.345")
     *
     *
     */
    public function __construct()
    {
        $arguments = func_get_args();

        // Point([lat, lon])
        if( count($arguments) == 1 && is_array($arguments[0]) && count($arguments[0]) == 2 ){
            $this->lat = $arguments[0][0];
            $this->lon = $arguments[0][1];

        // Point("lat, lon")
        }elseif( count($arguments) == 1 && is_string($arguments[0]) ){
            list($lat, $lon) = $this->parsePoint($arguments[0]);
            $this->lat = $lat;
            $this->lon = $lon;

        // Point("lat#lon", ["#"])
        }elseif( count($arguments) == 2 && is_string($arguments[0]) && is_array($arguments[1]) ){
            list($lat, $lon) = $this->parsePoint($arguments[0], $arguments[1][0]);
            $this->lat = $lat;
            $this->lon = $lon;

        // Point(lat, lon)
        }elseif( count($arguments) == 2 && ! is_array($arguments[1]) ){
            $this->lat = (float) $arguments[0];
            $this->lon = (float) $arguments[1];

        }else{
            throw new GeoSpatialException("Point object cannot be instantiated, wrong format.");
        }
    }

    private function parsePoint($points, $separator = ",")
    {
        $p = explode($separator, trim($points));
        if(count($p) != 2)
            throw new GeoSpatialException('Error creating Point from string ' . $points);

        return [ $p[0], $p[1] ];
    }

    /*
    |--------------------------------------------------------------------------
    | Distance between points
    |--------------------------------------------------------------------------
    |
    | You can get the great circle distance (https://en.wikipedia.org/wiki/Great-circle_distance)
    | between two points  using one of the providers.
    |
    */
    public static function distance(Point $p1, Point $p2, $provider = "haversine")
    {
        $distance = 0;

        if( ! in_array($provider, self::$greatCircleproviders))
            throw new GeoSpatialException('Great circle distance provider not found');

        if( $provider === "haversine" )
            $distance = self::haversineGreatCircleDistance($p1, $p2);

        elseif( $provider === "vicenty" )
            $distance = self::vincentyGreatCircleDistance($p1, $p2);

        return $distance;
    }

    private static function vincentyGreatCircleDistance(Point $from, Point $to, $earthRadius = 6371000){
        // convert from degrees to radians
        $latFrom = deg2rad($from->lat);
        $lonFrom = deg2rad($from->lon);
        $latTo = deg2rad($to->lat);
        $lonTo = deg2rad($to->lon);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
            pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }

    private static function haversineGreatCircleDistance(Point $from, Point $to, $earthRadius = 6371000){
        // convert from degrees to radians
        $latFrom = deg2rad($from->lat);
        $lonFrom = deg2rad($from->lon);
        $latTo = deg2rad($to->lat);
        $lonTo = deg2rad($to->lon);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }


    /**
     * TEMPORANEA
     *
     * @param $address
     * @return array|null
     * @throws GeoSpatialException
     */
    public static function georeverse($address)
    {
        $api_url = "https://maps.google.com/maps/api/geocode/json?language=it&&address=".urlencode($address);
        $response = file_get_contents($api_url);
        if ($json = json_decode($response, true)) {

            if( $json['status'] != 'OK' )
                throw new GeoSpatialException($json['status']);

            return [
                $json['results'][0]['geometry']['location']['lat'],
                $json['results'][0]['geometry']['location']['lng']
            ];
        }
        return null;
    }

}
