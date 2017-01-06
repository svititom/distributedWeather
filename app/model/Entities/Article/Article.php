<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 28-Dec-16
 * Time: 23:09
 */
namespace App;
use Carbon\Carbon;
use Nette;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * @ORM\Entity
 */
class Article
{
	use Nette\SmartObject;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $title;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $content;

	/**
	 * @OneToMany(targetEntity="Comment", mappedBy="article")
	 * @var Comment[]
	 */
	protected $comments;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $createdOn;

	/**
	 * @return mixed
	 */
	public function getCreatedOn()
	{
		return $this->createdOn;
	}

	/**
	 * Article constructor.
	 * @param $title
	 * @param $content
	 */
	public function __construct($title, $content)
	{
		$this->title = $title;
		$this->content = $content;
		$this->createdOn = Carbon::now();
	}


	/**
	 * @return null|Comment[]
	 */
	public function getComments(){
		return $this->comments->toArray();
	}

	/**
	 * @param Comment $comment
	 */
	public function addComment($comment){
		$this->comments[] = $comment;
	}


	public function getId(){
		return $this->id;
	}
	/**
	 * @return null|string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return null|string
	 */
	public function getContent()
	{
		return $this->content;
	}


	public function update($title, $content)
	{
		$this->title = $title;
		$this->content = $content;
	}


}