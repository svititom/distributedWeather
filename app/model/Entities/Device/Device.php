<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 29-Dec-16
 * Time: 17:44
 */

namespace App\Entities;
use app\model\Entities\BaseEntity;
use App\Entities\Measurement;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use App\Entities\User;
use Nette\Security\Passwords;


/**
 * Class Device
 * @package App\Model\Entities\Device
 * @ORM\Entity
 */
class Device extends BaseEntity
{
    //TODO Add store location for static devices (loc doesn't change)
	use \Nette\SmartObject;

    /**
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     * @var string
     */
    private $id;


    public function getId() :string{
        return $this->id;
    }


    /**
     * @ORM\ManyToOne(targetEntity="App\Entities\User", inversedBy="devices")
     */
    protected $user;

	/**
	 * @ORM\Column(type="string")
     *
	 */
	protected $name;
    /**
     * @ORM\Column(type="string")
     */
    protected $model;

    /**
	 * @ORM\Column(type="string")
	 */
	protected $vendor;
	//Accuracies will be absolute (might need to be added to the measurement, but I imagine it will be constant over teh values)
	/**
	 * @ORM\Column(type="float")
	 */
	protected $temperatureAccuracy;
	/**
	 * @ORM\Column(type="float")
	 */
	protected $humidityAccuracy;
	/**
	 * @ORM\Column(type="float")
	 */
	protected $pressureAccuracy;

	/**
	 * @OneToMany(targetEntity="App\Entities\Measurement", mappedBy="device")
	 */
	protected $measurements;

	/**
     * @var string
     * @ORM\Column(type="string")
     */
	protected $authToken;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $authTokenExpiry;

    /**
     * Device constructor.
     * @param $user
     * @param $name
     * @param $model
     * @param $vendor
     * @param $temperatureAccuracy
     * @param $humidityAccuracy
     * @param $measurements
     */
    public function __construct(User $user, $name, $model, $vendor, $temperatureAccuracy, $humidityAccuracy, $pressureAccuracy)
    {
        $this->user = $user;
        $this->name = $name;
        $this->model = $model;
        $this->vendor = $vendor;
        $this->temperatureAccuracy = $temperatureAccuracy;
        $this->humidityAccuracy = $humidityAccuracy;
        $this->pressureAccuracy = $pressureAccuracy;
        $this->updateAuthToken($user->getId());
    }

    public function updateAuthToken(string $userId){
        $authTokenVersion = 1;
        $expiry = new Carbon('next week');

        //We might want to make the token half decryptable in the future, to verify it w/o accesing the db
        $tokenUnencrypted = "exp=" . $expiry . ", ver=" . $authTokenVersion . ", " . Passwords::hash($userId);
        $tokenEncrypted = sha1($tokenUnencrypted);

        $this->authToken = $tokenEncrypted;
        $this->authTokenExpiry = $expiry;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @return mixed
     */
    public function getTemperatureAccuracy()
    {
        return $this->temperatureAccuracy;
    }

    /**
     * @return mixed
     */
    public function getHumidityAccuracy()
    {
        return $this->humidityAccuracy;
    }

    /**
     * @return mixed
     */
    public function getPressureAccuracy()
    {
        return $this->pressureAccuracy;
    }


    /**
     * @return mixed
     */
    public function getAuthToken()
    {
        return $this->authToken;
    }

    /**
     * @return mixed
     */
    public function getAuthTokenExpiry()
    {
        return  Carbon::instance($this->authTokenExpiry);
    }

    public function addMeasurement(Measurement $measurement)
    {
        //todo validate measurements
        $this->measurements[] = $measurement;
    }

    /**
     * @return mixed
     * //todo CHANGE TO LAZY LOAD!
     */
    public function getMeasurements()
    {
        return $this->measurements->toArray();
    }

}
class InvalidDeviceSettingsException extends \Exception {}