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
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;
use App\Entities\User;


/**
 * Class Device
 * @package App\Model\Entities\Device
 * @ORM\Entity
 */
class Device extends BaseEntity
{
	use \Nette\SmartObject;
	use Identifier;

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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAuthToken() : ?string
    {
        return $this->authToken;
    }


}
