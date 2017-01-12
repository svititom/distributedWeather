<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 12-Jan-17
 * Time: 13:04
 */

namespace app\model\Entities;



class BaseEntity
{
	use ArrayIterator;
}

trait ArrayIterator
{
	public function getIterator()
	{
		$reflectionClass = new \ReflectionClass($this);
		$properties = $reflectionClass->getProperties();
		$arrayRepresentation = [];

		foreach($properties as $property) {
			$name = $property->getName();

			if ($property->isPublic() && !$property->isStatic()) {
				$arrayRepresentation[$name] = $this->$name;
				continue;
			}

			if ($reflectionClass->hasMethod($method = 'get' . ucfirst($name)) || $reflectionClass->hasMethod($method = 'is' . ucfirst($name))) {
				$arrayRepresentation[$name] = $this->$method();
			}
		}
		return new \ArrayIterator($arrayRepresentation);
	}

	public function toArray() {
		return iterator_to_array($this->getIterator());
	}
}