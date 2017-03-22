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
use Doctrine\ORM\NoResultException;

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
     */
    public function findDeviceById(string $id): ? Device
    {
        return $this->em->getRepository(Device::class)->findOneBy(["id" => $id]);
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
    public function getDeviceByName(string $username, string $devicename) : ?Device{
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
        /**
         * Disclaimer: it's not my fault if you're dumb.
         * I'm lazy to save it in database.
         */
//        const PLACEHOLDER_API_ACCESS_TOKEN_DO_NOT_USE_ON_PRODUCTION = 'abcd';
        $device = $this->findDeviceById($deviceId);
        if ($device == null) {
            throw new NoSuchDeviceException();
        }
        //todo make sure this removes it from the Users list
        $this->em->remove($device);
        $this->em->flush();
    }

    public function updateDeviceAuthToken(string $username, string $devicename)
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

}

class NoSuchDeviceException extends \Exception{}