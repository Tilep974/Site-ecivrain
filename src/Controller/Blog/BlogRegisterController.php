<?php
namespace Livre\Controller\Blog;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Livre\Domain\User;
use Livre\Form\Type\UserType;

class BlogRegisterController {
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
     * Log the user in
     *
     * @param Livre\Domain\User $user User registering
     * @param Application $app Silex application
     */
    private function logUserIn(User $user, Application $app) {
        // log the user in
        $token = new UsernamePasswordToken($user, null, 'secured', array('ROLE_USER'));
        $app['security.token_storage']->setToken($token);
        $app['session']->set('_security_main', serialize($token));
    }





    /**
     * Register user controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function registerAction(Request $request, Application $app) {
        $user = new User();

        $userForm = $app['form.factory']->create(UserType::class, $user);

        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            if (!$app['dao.user']->findByUsername($user->getUsername())) {
                if (!$app['dao.user']->findByEmail($user->getEmail())) {
                    // generate a random salt value
                    $salt = substr(md5(time()), 0, 23);
                    $user->setSalt($salt);

                    //encode the plain password
                    $this->encodePassword($user, $app);

                    // initialize a role for the user
                    $user->setRole('ROLE_MEMBER');

                    // initialize a ban status for the user
                    $user->setIsActive(true);

                    $app['dao.user']->save($user);

                    //log the user in
                    $this->logUserIn($user, $app);

                    // flash message to thank the user + home page redirect
                    $app['session']->getFlashBag()->add('success', 'Merci, vous êtes désormais inscrit.');
                    return $app->redirect($app['url_generator']->generate('home'));
                }
                else {
                    $app['session']->getFlashBag()->add('error', 'Cette adresse email est déjà utilisée.');
                }
            }
            else {
                $app['session']->getFlashBag()->add('error', 'Ce pseudo est déjà utilisé.');
            }
        }

        return $app['twig']->render('register.html.twig', array(
            'title' => 'Rejoignez-nous',
            'userForm' => $userForm->createView()
        ));
    }
}
