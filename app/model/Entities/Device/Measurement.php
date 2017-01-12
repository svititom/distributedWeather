<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 29-Dec-16
 * Time: 17:44
 */

namespace App\Model\Entities\Device;
use app\model\Entities\BaseEntity;
use App\Model\Entities\Device\Device;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\MagicAccessors;


/**
 * @ORM\Entity
 */

class Measurement extends BaseEntity
{
	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="Device", inversedBy="Measurement")
	 */
	protected $device;
	/**
	 * @ORM\column(type="float")
	 */
	protected $temperature;
	/**
	 * @ORM\column(type="float")
	 */
	protected $humidity;
	/**
	 * @ORM\column(type="float")
	 */
	protected $pressure;
	//todo find a better way to represent locations ...
	/**
	 * @ORM\column(type="string")
	 */
	protected $location;
	/**
	 * @ORM\column(type="string")
	 */
	protected $locationAccuracy;

	/**
	 * @ORM\column(type="datetime")
	 */
	protected $datetime;

}