<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 29-Dec-16
 * Time: 13:04
 */

namespace App\Entities;

use App\Model\DuplicateNameException;
use app\model\Entities\BaseEntity;
use App\Entities\Device;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Nette\Security\Passwords;
use Carbon\Carbon;
use Nette\Utils\Arrays;


/**
 * @ORM\Entity
 */
class User extends BaseEntity
{
    //use Identifier;
    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    private $id;


    /**
	 * User constructor.
	 * @param $name
	 * @param $email
	 * @param $password
	 * @param string $role
	 */
	public function __construct($name, $email, $password, $role = self::ROLE_USER)
	{
		$this->name = $name;
		$this->email = $email;
		$this->passwordHash = Passwords::hash($password);
		$this->verificationHash = md5(rand(0,10000));
		$this->role = $role;
		$this->userVerified = false;
		$this->verificationHashExpiry = Carbon::now();
	}

	/**
	 * @param $password
	 * @return bool
	 */
	public function authenticate($password){
		if(!Passwords::verify($password, $this->passwordHash)){
			//if password is incorrect
			return false;
		} elseif (Passwords::needsRehash($this->passwordHash)){
			//if password is correct and needs rehash
			$this->passwordHash = Passwords::hash($password);
		}
		return true;
	}
	//todo change verification to exceptions ...
	/**
	 * @param $hash
	 * @return bool
	 */
	public function isVerificationHashValid($hash){
		$now = Carbon::now();
		return ($now->gt($this->verificationHashExpiry)
			&& $this->verificationHash == $hash);
	}
	/**
	 * Used to change users password after sending reset mail
	 * @param $email
	 * @param $hash
	 * @param $password
	 */
	public function resetPassword($hash, $password){
		if(!$this->isVerificationHashValid($hash)){
			throw new InvalidVerificationHashException();
		}
		//hash is valid, we can proceed
		$this->passwordHash = Passwords::hash($password);
	}

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;
	/**
	 * @ORM\Column(type="string", unique=true)
	 */
	protected $email;
	/**
	 * @ORM\Column(type="string")
	 */
	protected $role;

    /**
     * @OneToMany(targetEntity="App\Entities\Device", mappedBy="user")
     * @var Device[]
     */
	protected $devices;


	/**
	 * @ORM\Column(type="string", length=255)
	 * @var string
	 */
	protected $passwordHash;
	/**
	 * @ORM\Column(type="string", length=255)
	 */
	protected $verificationHash;
	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $verificationHashExpiry;
	/**
	 * @ORM\Column(type="boolean")
	 * @var bool
	 */
	protected $userVerified;




	const ROLE_USER = 'user';
	const ROLE_MEMBER = 'member';
	const ROLE_ADMIN = 'admin';

	/**
	 * @param int $hours optionally number of hours to expiry
	 * @return string - verification hash
	 */
	public function generateVerificationHash($hours = 24)
	{
		$expiry = Carbon::now();
		$expiry->addHours($hours);
		$this->verificationHashExpiry = $expiry;

		$this->verificationHash = md5(rand(0,10000));
		return $this->verificationHash;
	}

	/**
	 * checks whether hash is valid and in time, if yes, sets users status to verified
	 * @param $hash
	 * @return bool false is failure
	 */
	public function verify($hash){
		if($this->isVerificationHashValid($hash)){
			$this->verificationHash = '*'; //* cannot be generated by md5 i.e. blocks the hash until new is generated
			$this->userVerified = true;
			return true;
		}
		return false;
	}
	public function getRole(){
		return $this->role;
	}

    /**
     * @return string
     */
	public function getId() :string {
	    return $this->id;
    }
    /**
     * @param string $deviceName
     * @return Device|null
     */
	public function findDeviceByName(string $deviceName){
	    return Arrays::get($this->devices, $deviceName);
    }

    /**
     * @param string $deviceName
     * @return bool
     */
    public function hasDevice(string $deviceName)
    {
        return $this->devices[$deviceName] != null;
        //return Arrays::get($this->devices, $deviceName) != null;
    }

    /**
     * @param Device $device
     * @throws DuplicateNameException
     */
	public function addDevice(Device $device){
	    if($this->hasDevice($device->getName())){
	        throw new DuplicateNameException();
        }
	    $this->devices[$device->getName()] = $device;
    }

    /**
     * @return Device[]
     */

    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    public function removeDevice(string $deviceName){
       unset($this->devices[$deviceName]);
    }



}

class InvalidVerificationHashException extends \Exception
{}
