<?php

// src/Security/LegacyAuthenticator.php
namespace App\Security;

use App\Repository\Cms\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class LegacyAuthenticator extends AbstractAuthenticator
{
    private $userRepository;
    private $security;

    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->get('_route') === 'cms_login' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        // Retrieve username and password from the login form
        $password = $request->getPayload()->get('_password');
        $username = $request->getPayload()->get('_username');
        $csrfToken = $request->getPayload()->get('_csrf_token');

        // Fetch the user based on the username
        $user = $this->userRepository->findOneBy(['email' => $username]);

        // Check if the user exists
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Invalid username or password.' . $username . ' - ' . $password);
        }

        // Check if the password matches the stored plain text password (Not recommended in production!)
        if ($password !== $user->getPassword()) {
            throw new BadCredentialsException('Invalid username or password. ' . $user->getPassword() . ' - ' . $password);
        }

        // Return the user token upon successful authentication
        return new SelfValidatingPassport(new UserBadge($user->getEmail()));

//        return new Passport(
//            new UserBadge($username),
//            new PasswordCredentials($password),
//            [new CsrfTokenBadge('login', $csrfToken)]
//        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}