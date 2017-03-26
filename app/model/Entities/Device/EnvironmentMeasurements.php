<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 23-Mar-17
 * Time: 00:01
 */

namespace App\Entities;


interface EnvironmentMeasurements
{
     const
        KEY_TEMPERATURE   = 'temperature',
        KEY_PRESSURE      = 'pressure',
        KEY_HUMIDITY      = 'humidity',
        KEY_LOCATION      = 'location',
        KEY_LOCATION_ACC  = 'locationaccuracy',
        KEY_DATETIME      = 'datetime';


  //  public function getSupportedMeasurements();
}