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
     * @param $deviceId
     * @throws NoSuchDeviceException
     */
    public function removeDevice(string $deviceId)
    {
        $device = $this->findDeviceById($deviceId);
        if ($device == null) {
            throw new NoSuchDeviceException();
        }
        //todo make sure this removes it from the Users list
        $this->em->remove($device);
        $this->em->flush();
    }

}

class NoSuchDeviceException extends \Exception{}