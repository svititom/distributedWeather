<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 29-Dec-16
 * Time: 17:44
 */

namespace App\Model\Entities\Device;
use app\model\Entities\BaseEntity;
use App\Model\Entities\Device\Measurement;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;


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
	 * @ORM\Column(type="string")
	 */
	protected $name;
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
	 * @OneToMany(targetEntity="App\Model\Entities\Device\Measurement", mappedBy="device")
	 */
	protected $measurements;


}
