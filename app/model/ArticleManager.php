<?php
/**
 * Created by PhpStorm.
 * User: swith
 * Date: 20-Dec-16
 * Time: 14:17
 */


namespace App\Model;

use Nette;

class ArticleManager
{
    use Nette\SmartObject;

    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getPublicArticles()
    {
        return $this->database->table('posts')
           // ->where('created_at <', new \DateTime())
            ->order('created_at DESC');
    }
}