<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 04-Jan-17
 * Time: 17:17
 */

namespace App;

use Nette;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToOne;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * Class Comment
 * @package App
 * @ORM\Entity
 */
class Comment
{

	use Nette\SmartObject;
	use Identifier;

	/**
	 * @ORM\ManyToOne(targetEntity="Article", inversedBy="comments")
	 */
	protected $article;

	/**
	 * @param mixed $article
	 */
	public function setArticle($article)
	{
		$this->article = $article;
	}
	/**
	 * @var
	 * @ORM\Column(type="string")
	 */
	protected $userName;
	/**
	 * @var
	 * @ORM\Column(type="string")
	 */
	protected $email;
	/**
	 * @var
	 * @ORM\Column(type="string")
	 */
	protected $content;

	/**
	 * @return mixed
	 */
	public function getUserName()
	{
		return $this->userName;
	}

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}


	/**
	 * Comment constructor.
	 * @param $articleId
	 * @param $userName
	 * @param $email
	 * @param $content
	 */
	public function __construct($userName, $email, $content)
	{
		$this->userName = $userName;
		$this->email = $email;
		$this->content = $content;
	}

}