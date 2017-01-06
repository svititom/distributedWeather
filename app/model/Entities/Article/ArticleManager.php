<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 20-Dec-16
 * Time: 14:17
 */


namespace App\Model;

use App\Article;
use App\Comment;
use Doctrine\Common\Cache\ArrayCache;
use Kdyby\Doctrine\EntityManager;
use Nette;

class ArticleManager
{
    use Nette\SmartObject;

	/**
	 * @var EntityManager
	 */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

	/**
	 * @return Article[]
	 */
    public function getPublicArticles()
    {
    	return $this->em->getRepository(Article::class)->findAll();

    }

	/**
	 * @param $id
	 * @return null|Article
	 */
    public function getArticleById($id){
    	return $this->em->getRepository(Article::class)->find($id);
	}

	/**
	 * @param $id Article Id
	 * @param Comment $comment
	 */
	public function addComment($id, $comment){
		$article = $this->getArticleById($id);
		if($article){
			$article->addComment($comment);
			$comment->setArticle($article);
			$this->em->persist($comment);
			$this->em->flush($comment);
			$this->em->flush($article);

		}
	}

	public function deletArticle($articleId){
		$article = $this->getArticleById($articleId);
		foreach ($article->getComments() as $comment){
			$this->em->remove($comment);
		}
		$this->em->remove($article);
		$this->em->flush();
	}

	/**
	 * @param $articleId
	 * @param $values content and id
	 */
	public function updateArticle($articleId, $title, $content) {
		$article = $this->getArticleById($articleId);
		$article->update($title, $content);
		$this->em->flush($article);
	}

	/**
	 * @param $title
	 * @param $content
	 */
	public function createArticle($title, $content){
		$article = new Article($title, $content);
		$this->em->persist($article);
		$this->em->flush($article);
	}
}