<?php
namespace Livre\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;

class BlogControllerProvider implements ControllerProviderInterface {
    public function connect(Application $app) {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];




        // Home page
        $controllers->get('/', "Livre\Controller\Blog\BlogMainController::indexAction")->bind('home');

        //Login page
        $controllers->match('/connexion', "Livre\Controller\Blog\BlogMainController::loginAction")->bind('login');


        //Individual article pages with comments
        //match() for POST+GET
        $controllers->match('/article/{id}', "Livre\Controller\Blog\BlogArticleController::articleAction")->bind('article');

        //Delete a comment (via user)
        $controllers->match('/article/commentaire/supprimer', "Livre\Controller\Blog\BlogArticleController::deleteCommentAction")->bind('user_comment_delete');

        //Edit a comment (via user)
        $controllers->match('/article/commentaire/modifier', "Livre\Controller\Blog\BlogArticleController::editCommentAction")->bind('user_comment_edit');

        //Register page
        $controllers->match('/inscription', "Livre\Controller\Blog\BlogRegisterController::registerAction")->bind('register');
		
        return $controllers;
    }
}
