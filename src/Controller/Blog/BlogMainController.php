<?php
namespace Livre\Controller\Blog;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BlogMainController {
    /**
     * Home page controller.
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app) {
        $articles = $app['dao.article']->findLasts();

        return $app['twig']->render('index.html.twig', array('articles' => $articles));
    }

    /**
     * Posts list controller.
     *
     * @param Application $app Silex application
     */
    public function postsListAction(Application $app) {
        $articles = $app['dao.article']->findAll();

        return $app['twig']->render('postsList.html.twig', array('articles' => $articles));
    }

    /**
     * About page controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function aboutAction(Request $request, Application $app) {
        return $app['twig']->render('about.html.twig', array(
            'request' => $request
        ));
    }

    /**
     * User login controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function loginAction(Request $request, Application $app) {
        return $app['twig']->render('login.html.twig', array(
            'error' => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username')
        ));
    }
}
