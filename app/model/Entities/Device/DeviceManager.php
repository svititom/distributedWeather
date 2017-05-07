<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 18-Mar-17
 * Time: 16:08
 */

namespace App\Entities;


use App\Entities\Device;
use App\Entities\User;
use App\Model\DuplicateNameException;
use App\Model\UserManager;
use Carbon\Carbon;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NoResultException;
use App\Entities\Measurement;
use Kdyby;
use Kdyby\Doctrine\QueryObject;
use Kdyby\Doctrine\ResultSet;

class DeviceManager
{
    /**
     * @var \Kdyby\Doctrine\EntityManager
     */
    public $em;

    /**
     * @var UserManager
     */
    public $userManager;

    public function __construct(\Kdyby\Doctrine\EntityManager $em, UserManager $userManager)
    {
        $this->em = $em;
        $this->userManager = $userManager;
    }

    /**
     * @param string $id
     * @return \App\Entities\Device|null
     * todo NOT SECURE! Anyone can access atm
     */
    public function findDeviceById(string $id)
    {
        return $this->em->getRepository(Device::class)->findOneBy(['id' => $id]);
    }

    /**
     * @param string $token
     * @return \App\Entities\Device|null
     */
    private function findDeviceByToken(string $token)
    {
        return $this->em->getRepository(Device::class)->findOneBy(['authToken' => $token]);
    }


    /**
     * @param User $user
     * @param $deviceName
     * @param $model
     * @param $vendor
     * @param $tempAccuracy
     * @param $humidAccuracy
     * @param $presAccuracy
     * @throws DuplicateNameException
     */
    public function addDevice(string $username, $deviceName, $model, $vendor, $tempAccuracy, $humidAccuracy, $presAccuracy)
    {
        if ($this->userManager->userHasDevice($username, $deviceName)) {
            throw new DuplicateNameException();
        }
        //there most probably is a better way to do this ....
        $device = new Device($this->userManager->findUserById($username),
            $deviceName, $model, $vendor, $tempAccuracy, $humidAccuracy, $presAccuracy);

        $this->em->persist($device);
        $this->em->flush($device);

        $this->userManager->addDevice($username, $device);
    }

    /**
     * @param \App\Entities\User $user
     * @return mixed
     * Instant load
     */
    public function getUsersDevices(User $user)
    {
        return $user->getDevices()->toArray();
    }

    /**
     * @param string $username
     * @param string $devicename
     * @return \App\Entities\Device|null
     */
    public function getDeviceByName(string $username, string $devicename)
    {
        return $this->em->getRepository(Device::class)
                    ->findOneBy(['user.name' => $username,
                                 'name' => $devicename]);
    }

    /**
     * @param string $username
     * @param string $devicename
     * @return array
     * @throws NoSuchDeviceException
     */
    public function getDeviceAuthToken(string $username, string $devicename) : array
    {
        $device = $this->getDeviceByName($username, $devicename);
        if ($device == null){
            throw new NoSuchDeviceException();
        }
        return ['token' => $device->getAuthToken(),
                'expiration' => $device->getAuthTokenExpiry()];
    }

    /**
     * @param $deviceId
     * @throws NoSuchDeviceException
     */
    public function removeDevice(string $deviceId)
    {
        $device = $this->findDeviceById($deviceId);
        if ($device == null) {
            throw new NoSuchDeviceException();
        }
        $this->em->remove($device);
        $this->em->flush();
    }

    /**
     * @param string $username
     * @param string $devicename
     * @return array
     * @throws NoSuchDeviceException
     */
    public function updateDeviceAuthToken(string $username, string $devicename) : array
    {
        $device = $this->getDeviceByName($username, $devicename);
        if ($device == null){
            throw new NoSuchDeviceException();
        }
        $device->updateAuthToken($username);
        $this->em->flush($device);
        return ['token' => $device->getAuthToken(),
            'expiration' => $device->getAuthTokenExpiry()];

    }

    /**
     * @param string $token
     * @return \App\Entities\Device
     * @throws InvalidAuthTokenException
     */
    private function verifyToken(string $token) : Device
    {
            $device = $this->findDeviceByToken($token);
            //if no device found, the token is invalid
            if ($device == null){
                throw new InvalidAuthTokenException("Invalid token");
            }
            if ($device->getAuthTokenExpiry()->lt(Carbon::now())){
                throw new InvalidAuthTokenException("Expired token");
            }
            return $device;
    }

    /**
     * @param string $token
     * @param array $measurements
     */
    public function addMeasurement(string $token, array $measurements)
    {
        $device = $this->verifyToken($token);
        $measurement = new Measurement($device, $measurements);
        $device->addMeasurement($measurement);

        $this->em->persist($measurement);
        $this->em->flush($measurement);
        $this->em->flush($device);

    }
    public function getSupportedMeasurements() : array
    {
        return [
            Measurement::KEY_TEMPERATURE,
            Measurement::KEY_PRESSURE,
            Measurement::KEY_HUMIDITY,
            Measurement::KEY_LOCATION,
            Measurement::KEY_LOCATION_ACC,
            Measurement::KEY_DATETIME
        ];
    }

    public function getDeviceMeasurements(string $deviceId) : array
    {
        $query = $this->em->createQueryBuilder()
            ->select('m.datetime, m.temperature, m.humidity, m.pressure')->from(Measurement::class, 'm')
            ->innerJoin('m.device', 'd')
            ->where('d.id = :deviceId')->setParameter('deviceId', $deviceId)
            ->getQuery();
        $measurements = $query->getResult(AbstractQuery::HYDRATE_OBJECT);
        $temp = [];
        $pres = [];
        $humi = [];
        $dates = [];
        //$date = 0;
        foreach($measurements as $measurement){
            $date = $measurement["datetime"]->format('U');
            $dates[]= $date;
            $temp[] = $measurement["temperature"];
            $humi[] = $measurement["humidity"];
            $pres[] = $measurement["pressure"];
            //$date++;
        }

        return array("temperature" => $temp,
                    "humidity" => $humi,
                    "pressure" => $pres,
                    "datetime" => $dates);
    }


    /**
     * @param array $devices  //Device or device id
     * @param Carbon $timeout relative e.g. 10 minutes since last measurement
     * @return array
     */
    public function areDevicesActive(array $devices) : array
    {
        $result = [];
        foreach($devices as $device){
            if (!is_string($device)){
                //we got the device $id
                $device = $device->getId();
            }
            $qb = $this->em->createQueryBuilder()
                ->select('MAX(m.datetime)')->from(Device::class, 'd')
                ->andWhere('d.id = :deviceId')->setParameter('deviceId', $device)
                ->innerJoin('d.measurements', 'm');
            $resultSet = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SINGLE_SCALAR);
            if ($resultSet == ""){
                $result[] = false;
            } else {
                $result[] = (new Carbon($resultSet))->addMinute(15)->gte(Carbon::now());
            }
        }
        return $result;
    }

}

class NoSuchDeviceException extends \Exception{}
class InvalidAuthTokenException extends \Exception{}

