<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 29-Dec-16
 * Time: 17:44
 */

namespace App\Entities;
use app\model\Entities\BaseEntity;
use App\Entities\Device;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;


/**
 * @ORM\Entity
 */

class Measurement extends BaseEntity implements EnvironmentMeasurements
{
    use Identifier;

    /**
     * @ORM\ManyToOne(targetEntity="Device", inversedBy="Measurement")
     * @var Device
     */
    protected $device;
    /**
     * @ORM\Column(type="float")
     * @var float
     */
    protected $temperature;
    /**
     * @ORM\Column(type="float")
     * @var float
     */
    protected $humidity;
    /**
     * @ORM\Column(type="float")
     * @var float
     */
    protected $pressure;
    //todo find a better way to represent locations ...
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $location;
    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $locationAccuracy;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $datetime;

    /**
     * Measurement constructor.
     * @param \App\Entities\Device $device
     * @param array $measurements
     */
    public function __construct(Device $device, array $measurements)
    {
        $this->device = $device;
        //todo actually create the constructor ...
        $this->temperature      = $measurements[$this::KEY_TEMPERATURE];
        $this->pressure         = $measurements[$this::KEY_PRESSURE];
        $this->humidity         = $measurements[$this::KEY_HUMIDITY];
        $this->location         = $measurements[$this::KEY_LOCATION];
        $this->locationAccuracy = $measurements[$this::KEY_LOCATION_ACC];
        $this->datetime         = new Carbon($measurements[$this::KEY_DATETIME]);
    }

    /**
     * @return float
     */
    public function getTemperature(): float
    {
        return $this->temperature;
    }

    /**
     * @return float
     */
    public function getHumidity(): float
    {
        return $this->humidity;
    }

    /**
     * @return float
     */
    public function getPressure(): float
    {
        return $this->pressure;
    }

    /**
     * @return Carbon
     */
    public function getDatetime(): string
    {
        return $this->datetime->format('H:i:s Y-m-d');
    }

}