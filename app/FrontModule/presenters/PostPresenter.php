<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Http\IResponse;


class PostPresenter extends Nette\Application\UI\Presenter
{
    private $database;

    public function commentFormSucceeded($form, $values)
    {

    	$postId = $this->getParameter('postId');

        $this->database->table('comments')->insert([
            'post_id' => $postId,
            'name'  => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ]);

        $this->flashMessage('Thanks for commenting', 'success');
        $this->redirect('this');
    }

    protected function createComponentCommentForm()
    {

        $form = new Form;
        $form->addText('name', 'Name: ')
            ->setRequired();

        $form->addEmail('email', 'E-mail: ');
        $form->addTextArea('content', 'Comment')
            ->setRequired();


        $form->addSubmit('send', 'Post');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];
        return $form;
    }

    public function postFormSucceeded($form, $values)
    {
        $postId = $this->getParameter('postId');
        if($postId){
            $post = $this->database->table('posts')->get($postId);
            $post->update($values);
        } else {
            $post = $this->database->table('posts')->insert($values);
        }
        $this->flashMessage('Posted successfully!', 'success');
        $this->redirect('show',$post->id);
    }
    protected function createComponentPostForm()
    {
        $form = new Form;

        $form->addText('title', 'Title:')
            ->SetRequired();
        $form->addTextArea('content', 'Content:');

        $form->addSubmit('send', 'Post');

        $form->onSuccess[] = [$this, 'postFormSucceeded'];
        return $form;
    }
    public function actionCreate()
    {
        if(!$this->getUser()->isLoggedIn()){
            $this->redirect('Sign:in');
        }
        if(!$this->getUser()->isInRole('admirn')){
			$this->error('Only admins can edit posts', Nette\Http\IResponse::S403_FORBIDDEN);
		}
    }
    public function actionEdit($postId){
        if(!$this->getUser()->isLoggedIn()){
            $this->redirect('Sign:in');
        }
        if(!$this->getUser()->isInRole('admin')){
        	$this->error('Only admins can edit posts', Nette\Http\IResponse::S403_FORBIDDEN);
		}
        $post = $this->database->table('posts')->get($postId);
        if(!$post){
            $this->error('Article not found');
        }
        $this['postForm']->setDefaults($post->toArray());
    }

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function renderShow($postId)
	{
	    $post = $this->database->table('posts')->get($postId);
        if(!$post){
            $this->error('Page not found');
        }

        $this->template->post = $post;
		$comments = $post->related('comment')->order('created_at');
		if($comments){
			$this->template->comments = $comments;
		}
	}

}
