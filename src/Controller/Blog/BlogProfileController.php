<?php
namespace Livre\Controller\Blog;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Livre\Domain\ChangePassword;
use Livre\Domain\User;
use Livre\Form\Type\BasicUserDataType;
use Livre\Form\Type\UserPasswordType;
use Livre\Form\Type\UserDeleteType;

class BlogProfileController {
    /**
     * Fetch all articles related to the given comments
     *
     * @param array $comments All aff the user's comments
     * @param Application $app Silex application
     */
    private function fetchArticles($comments, Application $app) {
        foreach ($comments as $comment) {
            $articleId = $comment->getArticle()->getId();
            $articles[$articleId] = $app['dao.article']->find($articleId);
        }

        return $articles;
    }

    /**
     * Relog the user in
     *
     * @param Livre\Domain\User $user User being relogged
     * @param Application $app Silex application
     */
    private function relogUserIn(User $user, Application $app) {
        $oldToken = $app['security.token_storage']->getToken();
        $newToken = new UsernamePasswordToken($user, null, $oldToken->getProviderKey(),
    $oldToken->getRoles());
        $app['security.token_storage']->setToken($newToken);
        $app['session']->set('_security_main', serialize($newToken));
    }

    /**
     * Manage the basic user data form submission
     *
     * @param Livre\Domain\User $user The active user
     * @param Application $app Silex application
     */
    private function basicDataFormSubmission(User $user, Application $app) {
        if (!$app['dao.user']->findByUsername($user->getUsername(), $user->getId())) {
            if (!$app['dao.user']->findByEmail($user->getEmail(), $user->getId())) {
                //update the current user
                $app['dao.user']->save($user);

                // relog the user in
                //logged out automatically following name/email change
                $this->relogUserIn($user, $app);

                $app['session']->getFlashBag()->add('success', 'Votre profil a bien été mis à jour.');
            }
            else {
                $app['session']->getFlashBag()->add('error', 'Cette adresse email est déjà utilisée.');
            }
        }
        else {
            $app['session']->getFlashBag()->add('error', 'Ce pseudo est déjà utilisé.');
        }
    }

    /**
     * Manage the user password form submission
     *
     * @param Livre\Domain\ChangePassword $model ChangePassword entity
     * @param Livre\Domain\User $user The active user
     * @param Application $app Silex application
     */
    private function userPasswordFormSubmission(ChangePassword $model, User $user, Application $app) {
        $plainOldPassword = $model->getOldPassword();
        $plainNewPassword = $model->getNewPassword();

        // find the encoder for the user
        $encoder = $app['security.encoder_factory']->getEncoder($user);

        // test the entered plain password with the current encoded password
        if ($encoder->isPasswordValid($user->getPassword(), $plainOldPassword, $user->getSalt())) {
            $newPassword = $encoder->encodePassword($plainNewPassword, $user->getSalt());

            $user->setPassword($newPassword);
            $app['dao.user']->save($user);

            $app['session']->getFlashBag()->add('success', 'Votre mot de passe a bien été mis à jour.');
        }
        else {
            $app['session']->getFlashBag()->add('error', 'Votre mot de passe actuel ne correspond pas à votre entrée.');
        }
    }

    /**
     * Log the user in
     *
     * @param Livre\Domain\User $user User registering
     * @param Application $app Silex application
     */
    private function logUserOut(Application $app) {
        // log the user out
        $app['security.token_storage']->setToken(null);
        $app['session']->invalidate();
    }

    /**
     * Manage the user password form submission
     *
     * @param string $userPwd User's current password
     * @param Livre\Domain\User $user The active user
     * @param Application $app Silex application
     */
    private function userDeleteFormSubmission($userPwd, User $user, Application $app) {
        $plainPassword = $user->getPassword();

        // find the encoder for the user
        $encoder = $app['security.encoder_factory']->getEncoder($user);

        // test the entered plain password with the current encoded password
        if ($encoder->isPasswordValid($userPwd, $plainPassword, $user->getSalt())) {
            //Log the user out
            $this->logUserOut($app);

            // Delete the user
            $app['dao.user']->delete($user->getId());
            $app['session']->getFlashBag()->add('success', 'Votre compte a été supprimé avec succès.');

            // Redirect to home page
            return $app->redirect($app['url_generator']->generate('home'));
        }
        else {
            $app['session']->getFlashBag()->add('error', 'Votre mot de passe actuel ne correspond pas à votre entrée.');

            return $app->redirect($app['url_generator']->generate('profile', array('id' => $user->getId())));
        }
    }





    /**
     * Profile page controller.
     *
     * @param integer $id User id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function profileAction($id, Request $request, Application $app) {
        $changePasswordModel = new ChangePassword();

        $user = $app['dao.user']->find($id);

        $token = $app['security.token_storage']->getToken();

        if (is_object($token->getUser()) && $token->getUser()->getId() == $id) {
            // Allow to display all messages by user, sorted by article
            $comments = $app['dao.comment']->findAllByUser($id);
            $articles = array();
            if(!empty($comments))
                $articles = $this->fetchArticles($comments, $app);


            // Managing all necessary forms
            $basicUserDataForm = $app['form.factory']->createNamed('basicData', BasicUserDataType::class, $user);
            $userPasswordForm = $app['form.factory']->createNamed('userPassword', UserPasswordType::class, $changePasswordModel);
            $userDeleteForm = $app['form.factory']->createNamed('userDelete', UserDeleteType::class, $user);

            if ($request->isMethod('POST')) {
                //Managing the basic user data form submission
                $basicUserDataForm->submit($request->request->get($basicUserDataForm->getName()), false);
                if ($request->request->has($basicUserDataForm->getName())) {
                    if ($basicUserDataForm->isValid())
                        $this->basicDataFormSubmission($user, $app);
                }

                //Managing the user password form submission
                $userPasswordForm->submit($request->request->get($userPasswordForm->getName()), false);
                if ($request->request->has($userPasswordForm->getName())) {
                    if ($userPasswordForm->isValid())
                        $this->userPasswordFormSubmission($changePasswordModel, $user, $app);
                }

                //Managing the delete user form submission
                $userPwd = $user->getPassword();
                $userDeleteForm->submit($request->request->get($userDeleteForm->getName()), false);
                if ($request->request->has($userDeleteForm->getName())) {
                    if ($userDeleteForm->isValid())
                        return $this->userDeleteFormSubmission($userPwd, $user, $app);
                }
            }

            return $app['twig']->render('profile.html.twig', array(
                'title' => 'Profil',
                'basicUserDataForm' => $basicUserDataForm->createView(),
                'userPasswordForm' => $userPasswordForm->createView(),
                'userDeleteForm' => $userDeleteForm->createView(),
                'articles' => $articles,
                'comments' => $comments
            ));
        }
        else {
            return $app->redirect($app['url_generator']->generate('home'));
        }
    }
}
