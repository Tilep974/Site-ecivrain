<?php
namespace Livre\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Livre\Domain\Article;
use Livre\Form\Type\ArticleType;

class AdminArticlesController {
    /**
     * Add article controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function addArticleAction(Request $request, Application $app) {
        $article = new Article();

        $articleForm = $app['form.factory']->create(ArticleType::class, $article);
        $articleForm->handleRequest($request);
        if ($articleForm->isSubmitted() && $articleForm->isValid()) {
            $app['dao.article']->save($article);
            $app['session']->getFlashBag()->add('success', 'L\'article a été créé avec succès.');
        }

        return $app['twig']->render('article_form.html.twig', array(
            'title' => 'Créer un article',
            'request' => $request,
            'articleForm' => $articleForm->createView()
        ));
    }

    /**
     * Edit article controller.
     *
     * @param integer $id Article id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */

    public function editArticleAction($id, Request $request, Application $app) {
        $article = $app['dao.article']->find($id);

        $articleForm = $app['form.factory']->create(ArticleType::class, $article);
        $articleForm->handleRequest($request);
        if ($articleForm->isSubmitted() && $articleForm->isValid()) {
            $app['dao.article']->save($article);
            $app['session']->getFlashBag()->add('success', 'L\'article a été modifié avec succès.');
        }

        return $app['twig']->render('article_form.html.twig', array(
            'title' => 'Modifier l\'article',
            'request' => $request,
            'articleForm' => $articleForm->createView()
        ));
    }

    /**
     * Delete article controller.
     *
     * @param integer $id Article id
     * @param Application $app Silex application
     */
    public function deleteArticleAction($id, Application $app) {
        $article = $app['dao.article']->find($id);

        $errors = $app['validator']->validate($article);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $app['session']->getFlashBag()->add('error', $error->getMessage());
            }
        }
        else {
            // Delete the article
            $app['dao.article']->delete($id);
            $app['session']->getFlashBag()->add('success', 'L\'article a été supprimé avec succès.');
        }

        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }
}
