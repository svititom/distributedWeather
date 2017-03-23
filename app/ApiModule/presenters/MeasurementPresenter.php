<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 22-Mar-17
 * Time: 23:33
 */

namespace app\ApiModule\presenters;


use App\Entities\DeviceManager;
use App\Entities\InvalidAuthTokenException;
use Nette\Http\IResponse;
use Nette\Utils\Arrays;

class MeasurementPresenter extends BasePresenter
{

    /**
     * @var DeviceManager @inject
     */
    public $deviceManager;

    /**
     * Add new measurement to db
     */
    public function actionCreate()
    {
        $headers = $this->getHttpRequest()->getHeaders();
        if (!$token = $headers[$this::HEADER_AUTHORIZATION]){
            $this->error("Missing '" . $this::HEADER_AUTHORIZATION . "' header", \Nette\Http\IResponse::S400_BAD_REQUEST);
        }
        try {
            $values = $this->reduceArray($headers, $this->deviceManager->getSupportedMeasurements());
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), IResponse::S400_BAD_REQUEST);
        }
        try {
            $this->deviceManager->addMeasurement($token, $values);
        } catch (InvalidAuthTokenException $e){
            $this->error($e->getMessage(), IResponse::S401_UNAUTHORIZED);
        }
        //$this->payload->access_token =  $this->deviceManager->updateDeviceAuthToken($username, $deviceName);
        $this->success();
    }

    /**
     * @param array $arr
     * @param array $keys
     * @return array
     */
    public function reduceArray(array $arr, array $keys) : array
    {
        $output = null;
        $error = null;
        foreach ($keys as $key){
            if (array_key_exists($key, $arr)){
                $output[$key] = $arr[$key];
            } else {
                //$output[$key] = NULL;
                $error[] = $key;
            }
        }
        if ($error != null){

            throw new \InvalidArgumentException("Missing headers: " . implode(", ", $error));
        }
        return $output;
    }
}