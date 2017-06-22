<?php
namespace Livre\Controller\Admin;

use Silex\Application;

class AdminHomeController {
    public function compareComments($a, $b) {
        if ($a->getNumberFlags() == $b->getNumberFlags()) {
            return 0;
        }

        return ($a->getNumberFlags() > $b->getNumberFlags()) ? -1 : 1;
    }




    /**
     * Admin home page controller.
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app) {
        $articles = $app['dao.article']->findAll();
        $comments = $app['dao.comment']->findAll();
        $users = $app['dao.user']->findAll();

        $flagCounts = $app['dao.flag']->countByComments();

        foreach ($comments as $commentId => $comment) {
            foreach ($flagCounts as $flagId => $flag) {
                if ($flagId == $commentId)
                    $comment->setNumberFlags($flagCounts[$flagId]);
            }

            if (!$comment->getNumberFlags())
                $comment->setNumberFlags(0);
        }

        usort($comments, array($this, "compareComments"));

        return $app['twig']->render('admin.html.twig', array(
            'articles' => $articles,
            'comments' => $comments,
            'users' => $users,
            'flagCounts' => $flagCounts
        ));
    }
}
