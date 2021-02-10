<?php

namespace App\Security;

use App\Entity\Usuario; // your user entity
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class MyGoogleAuthenticator extends SocialAuthenticator {

    private $clientRegistry;
    private $em;
    private $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router) {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
    }

    public function supports(Request $request) {
        // continue ONLY if the current ROUTE matches the check ROUTE
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function getCredentials(Request $request) {
        // this method is only called if supports() returns true
        // For Symfony lower than 3.4 the supports method need to be called manually here:
        // if (!$this->supports($request)) {
        //     return null;
        // }

        return $this->fetchAccessToken($this->getFacebookClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        /** @var FacebookUser $facebookUser */
        $googleUser = $this->getFacebookClient()
                ->fetchUserFromToken($credentials);

        $email = $googleUser->getEmail();

        // 1) have they logged in with Facebook before? Easy!
        $existingUser = $this->em->getRepository(Usuario::class)
                ->findOneBy(['googleId' => $googleUser->getId()]);
        if ($existingUser) {
            return $existingUser;
        }

        // 2) do we have a matching user by email?
        $user = $this->em->getRepository(Usuario::class)
                ->findOneBy(['email' => $email]);

        if ($user) {
            $user->setGoogleId($googleUser->getId());
            $this->em->persist($user);
            $this->em->flush();
            return $user;
        }

        // 3) Maybe you just want to "register" them by creating
        // a User object

        $new_user = new Usuario();
        $new_user->setNombre($googleUser->getFirstName());
        $new_user->setApellidos($googleUser->getLastName());
        $new_user->setEmail($email);
        $new_user->setRoles("ROLE_USER");
        $new_user->setPassword(random_int(1, 99999) . "");
        $new_user->setFoto("default_img.jpg");
        $new_user->setTelefono("");

        $new_user->setGoogleId($googleUser->getId());

        $this->em->persist($new_user);
        $this->em->flush();

        return $new_user;
    }

    /**
     * @return FacebookClient
     */
    private function getFacebookClient() {
        return $this->clientRegistry
                        // "facebook_main" is the key used in config/packages/knpu_oauth2_client.yaml
                        ->getClient('google');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        // change "app_homepage" to some route in your app
        $targetUrl = $this->router->generate('app_login');

        return new RedirectResponse($targetUrl);

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     * This redirects to the 'login'.
     */
    public function start(Request $request, AuthenticationException $authException = null) {
        return new RedirectResponse(
                '/connect/', // might be the site, where users choose their oauth provider
                Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    // ...
}
