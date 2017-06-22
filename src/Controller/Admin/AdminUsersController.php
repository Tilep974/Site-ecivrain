<?php
namespace Livre\Controller\Admin;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Livre\Domain\User;
use Livre\Form\Type\UserType;
use Livre\Form\Type\UserEditBasicType;
use Livre\Form\Type\UserEditPasswordType;

class AdminUsersController {
    /**
     * Encode a plain password
     *
     * @param Livre\Domain\User $user User registering
     * @param Application $app Silex application
     */
    private function encodePassword(User $user, Application $app) {
        $plainPassword = $user->getPassword();

        // find the default encoder
        $encoder = $app['security.encoder.bcrypt'];

        // compute the encoded password
        $password = $encoder->encodePassword($plainPassword, $user->getSalt());
        $user->setPassword($password);
    }





    /**
     * Add user controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function addUserAction(Request $request, Application $app) {
        $user = new User();

        $userForm = $app['form.factory']->create(UserType::class, $user);

        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            if (!$app['dao.user']->findByUsername($user->getUsername(), $user->getId())) {
                if (!$app['dao.user']->findByEmail($user->getEmail(), $user->getId())) {
                    // generate a random salt value
                    $salt = substr(md5(time()), 0, 23);
                    $user->setSalt($salt);

                    //encode the plain password
                    $this->encodePassword($user, $app);

                    // initialize a ban status for the user
                    $user->setIsActive(true);

                    $app['dao.user']->save($user);
                    $app['session']->getFlashBag()->add('success', 'Le membre a été créé avec succès.');
                }
                else {
                    $app['session']->getFlashBag()->add('error', 'Cette adresse email est déjà utilisée.');
                }
            }
            else {
                $app['session']->getFlashBag()->add('error', 'Ce pseudo est déjà utilisé.');
            }
        }

        return $app['twig']->render('user_form.html.twig', array(
            'title' => 'Nouveau membre',
            'userForm' => $userForm->createView()
        ));
    }

    /**
     * Edit user controller.
     *
     * @param integer $id User id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function editUserAction($id, Request $request, Application $app) {
        $user = $app['dao.user']->find($id);

        $basicUserDataForm = $app['form.factory']->create(UserEditBasicType::class, $user);
        $userPasswordForm = $app['form.factory']->create(UserEditPasswordType::class, $user);

        if ($request->isMethod('POST')) {
            //Managing the basic user data form submission
            $basicUserDataForm->submit($request->request->get($basicUserDataForm->getName()), false);
            if ($request->request->has($basicUserDataForm->getName())) {
                if ($basicUserDataForm->isValid()) {
                    if (!$app['dao.user']->findByUsername($user->getUsername(), $user->getId())) {
                        if (!$app['dao.user']->findByEmail($user->getEmail(), $user->getId())) {
                            $app['dao.user']->save($user);
                            $app['session']->getFlashBag()->add('success', 'Les informations du membre ont été mises à jour avec succès.');
                        }
                        else {
                            $app['session']->getFlashBag()->add('error', 'Cette adresse email est déjà utilisée.');
                        }
                    }
                    else {
                        $app['session']->getFlashBag()->add('error', 'Ce pseudo est déjà utilisé.');
                    }
                }
            }

            //Managing the user password form submission
            $userPasswordForm->submit($request->request->get($userPasswordForm->getName()), false);
            if ($request->request->has($userPasswordForm->getName())) {
                if ($userPasswordForm->isValid()) {
                    $this->encodePassword($user, $app);

                    $app['dao.user']->save($user);
                    $app['session']->getFlashBag()->add('success', 'Le mot de passe du membre a été mis à jour avec succès.');
                }
            }
        }

        return $app['twig']->render('user_edit_form.html.twig', array(
            'title' => 'Modifier le membre',
            'basicUserDataForm' => $basicUserDataForm->createView(),
            'userPasswordForm' => $userPasswordForm->createView()
        ));
    }

    /**
     * Ban user controller.
     *
     * @param integer $id User id
     * @param Application $app Silex application
     */
    public function banUserAction($id, Application $app) {
        $user = $app['dao.user']->find($id);

        $errors = $app['validator']->validate($user);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $app['session']->getFlashBag()->add('error', $error->getMessage());
            }
        }
        else {
            // ban the user
            $app['dao.user']->ban($id);
            $app['session']->getFlashBag()->add('success', 'Le membre a été banni avec succès.');
        }

        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }

    /**
     * Unban user controller.
     *
     * @param integer $id User id
     * @param Application $app Silex application
     */
    public function unbanUserAction($id, Application $app) {
        $user = $app['dao.user']->find($id);

        $errors = $app['validator']->validate($user);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $app['session']->getFlashBag()->add('error', $error->getMessage());
            }
        }
        else {
            // Unban the user
            $app['dao.user']->unban($id);
            $app['session']->getFlashBag()->add('success', 'Le membre a été réinstitué avec succès.');
        }

        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }

    /**
     * Delete user controller.
     *
     * @param integer $id User id
     * @param Application $app Silex application
     */
    public function deleteUserAction($id, Application $app) {
        $user = $app['dao.user']->find($id);

        $errors = $app['validator']->validate($user);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $app['session']->getFlashBag()->add('error', $error->getMessage());
            }
        }
        else {
            // Delete the user
            $app['dao.user']->delete($id);
            $app['session']->getFlashBag()->add('success', 'Le membre a été supprimé avec succès.');
        }

        // Redirect to admin home page
        return $app->redirect($app['url_generator']->generate('admin'));
    }
}
