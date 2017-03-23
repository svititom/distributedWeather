<?php

namespace App\FrontModule\Presenters;

use App\Article;
use App\Comment;
use Kdyby\Doctrine\EntityManager;
use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;


class PostPresenter extends Nette\Application\UI\Presenter
{
    private $em;

	/** @var  \Instante\Bootstrap3Renderer\BootstrapFormFactory @inject */
	public $formFactory;
	public $ArticleManager;



	public function __construct(EntityManager $em, Model\ArticleManager $articleManager)
	{

		$this->em = $em;
		$this->ArticleManager = $articleManager;
	}

	public function commentFormSucceeded($form, $values)
    {
    /*	if(!$values->email){
    		$values->email = ' ';
		}
    */
    	$postId = $this->getParameter('postId');
		$comment = new Comment($values->name, $values->email, $values->content);
		$this->ArticleManager->addComment($postId, $comment);

		$this->flashMessage('Thanks for commenting', 'success');
        $this->redirect('this');
    }

       protected function createComponentDeleteForm()
    {
        $form = $this->formFactory->create();
        $form->addSubmit('delete', 'Delete article');
        $form->onSuccess[] = function ($form, $values){
            $this->ArticleManager->deleteArticle($this->getParameter('postId'));
            $this->flashMessage('Deleted successfully');
            $this->redirect('Homepage:');
        };
        return $form;
    }

	public function actionDelete($postId){
        //button provided by deleteform
	}

	protected function createComponentCommentForm()
    {

        $form = $this->formFactory->create();
        $form->addText('name', 'Name: ')
            ->setRequired();

        $form->addEmail('email', 'E-mail: ');
        $form->addTextArea('content', 'Comment')
            ->setRequired();


        $form->addSubmit('send', 'Post');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];
        return $form;
    }

	/**
	 * @param $form
	 * @param Nette\Utils\ArrayHash $values
	 */
    public function postFormSucceeded($form, $values)
    {
        $postId = $this->getParameter('postId');
		if($postId){
			$this->ArticleManager->updateArticle($postId, $values->title, $values->content);
        } else {
           	$postId = $this->ArticleManager->createArticle($values->title, $values->content);
		}
        $this->flashMessage('Posted successfully!', 'success');
        $this->redirect('show',$postId);
    }
    protected function createComponentPostForm()
    {
        $form = $this->formFactory->create();

        $form->addText('title', 'Title:')
            ->SetRequired();
        $form->addTextArea('content', 'Content:');

        $form->addSubmit('send', 'Post');
        $form->addSubmit('cancel', 'Cancel')
            ->setValidationScope(array())
            ->onClick[] = function($sender){
        $this->redirect('Homepage:');
    };

        $form->onSuccess[] = [$this, 'postFormSucceeded'];
        return $form;
    }
    public function actionCreate()
    {
        if(!$this->getUser()->isLoggedIn()){
            $this->redirect('Sign:in');
        }
        if(!$this->getUser()->isInRole('admin')){
			$this->error('Only admins can edit posts', Nette\Http\IResponse::S403_FORBIDDEN);
		}
    }
    public function actionEdit($postId){
        if(!$this->getUser()->isLoggedIn()){
            $this->redirect(':Front:Sign:in');
        }
        if(!$this->getUser()->isInRole('admin')){
        	$this->error('Only admins can edit posts', Nette\Http\IResponse::S403_FORBIDDEN);
		}

        $post = $this->ArticleManager->getArticleById($postId);
        if(!$post){
            $this->error('Article not found');
        }
        $this['postForm']->setDefaults([
        	'title' => $post->getTitle(),
			'content' => $post->getContent(),
		]);
    }


    public function renderShow($postId)
	{
		$post = $this->ArticleManager->getArticleById($postId);
	    if(!$post){
            $this->error('Page not found');
        }
        $this->template->post = $post;
		$comments = $post->getComments();
		$this->template->comments = $comments;

	}

}
