<?php

namespace Draw\DrawDemoBundle\User;

use Doctrine\Common\Persistence\ManagerRegistry;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Draw\DrawDemoBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface, ResourceOwnerInterface
{
    /**
     * @var ManagerRegistry
     */
    private $entityManager;

    /**
     * @var string
     */
    private $resourceOwnerName = "looklike";

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    private $resourceOwnerOptions = array(
        "user_response_class" => 'LookLike\Bundle\LookLikeBundle\User\UserResponse'
    );

    public function __construct(ManagerRegistry $entityManager, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @see UsernameNotFoundException
     *
     * @throws UsernameNotFoundException if the user is not found
     *
     */
    public function loadUserByUsername($username)
    {
        $user = $this->entityManager->getRepository("LookLikeBundle:User")->findOneBy(array("id" => $username));

        if (!$user) {
            $exception = new UsernameNotFoundException();
            $exception->setUsername($username);
            throw $exception;
        }


        if(!$user->pictureUrl) {
            $oAuthUser = $this->entityManager->getRepository("LookLikeBundle:OAuthUser")
                ->findOneBy(['resourceOwnerName' => 'facebook', 'user' => $user]);

            if($oAuthUser) {
                $user->pictureUrl = 'http://graph.facebook.com/' . $oAuthUser->username . '/picture';
                $this->entityManager->getManagerForClass("LookLikeBundle:User")->flush($user);
            }
        }

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return true;
    }

    /**
     * Loads the user by a given UserResponseInterface object.
     *
     * @param UserResponseInterface $response
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $resourceOwnerName = $response->getResourceOwner()->getName();
        $username = $response->getUsername();

        $oAuthUser = $this->entityManager->getRepository("LookLikeBundle:OAuthUser")
            ->findOneBy(compact('resourceOwnerName', 'username'));

        if (is_null($oAuthUser)) {
            $oAuthUser = $this->createOAuthUser($resourceOwnerName, $username, $this->generateRandomPassword());
        }

        $user = $oAuthUser->user;
        if(!$user->pictureUrl && $response->getProfilePicture()) {
            $user->pictureUrl = $response->getProfilePicture();
            $this->entityManager->getManagerForClass("LookLikeBundle:OAuthUser")->flush($user);
        }

        return $oAuthUser->user;
    }

    private function createOAuthUser($resourceOwnerName, $oAuthUsername, $password)
    {
        $user = new User();
        $user->password = $this->userPasswordEncoder->encodePassword($user, $password);

        $oAuthUser = new OAuthUser();
        $oAuthUser->resourceOwnerName = $resourceOwnerName;
        $oAuthUser->username = $oAuthUsername;
        $oAuthUser->user = $user;

        $manager = $this->entityManager->getManagerForClass("LookLikeBundle:OAuthUser");
        $manager->persist($oAuthUser);
        $manager->flush($oAuthUser);

        return $oAuthUser;
    }

    /**
     * Returns a $length random string
     * @param int $length
     * @return string
     */
    private function generateRandomPassword($length = 8)
    {
        $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

    /**
     * Retrieves the user's information from an access_token
     *
     * @param array $accessToken The access token
     * @param array $extraParameters An array of parameters to add to the url
     *
     * @return UserResponseInterface The wrapped response interface.
     */
    public function getUserInformation(array $accessToken, array $extraParameters = array())
    {
        $accessToken = json_decode($accessToken['access_token'], true);
        if (!array_key_exists('username', $accessToken)) {
            throw new AuthenticationException('Missing username.');
        }

        $username = $accessToken['username'];

        if (!array_key_exists('password', $accessToken)) {
            throw new AuthenticationException('Missing password.');
        }

        $password = $accessToken['password'];

        $user = $user = $this->entityManager->getRepository("LookLikeBundle:User")
            ->findOneBy(array("username" => $username));

        if (is_null($user)) {
            $user = $this->createOAuthUser($this->getName(), $username, $password)->user;
            $user->username = $accessToken['username'];
            $this->entityManager->getManagerForClass("LookLikeBundle:OAuthUser")->flush($user);
        } elseif (!$this->userPasswordEncoder->isPasswordValid($user, $password)) {
            throw new AuthenticationException('Password error.');
        }

        $response = new UserResponse($accessToken['username']);
        $response->setResourceOwner($this);

        return $response;
    }

    /**
     * Returns the provider's authorization url
     *
     * @param string $redirectUri The uri to redirect the client back to
     * @param array $extraParameters An array of parameters to add to the url
     *
     * @return string The authorization url
     */
    public function getAuthorizationUrl($redirectUri, array $extraParameters = array())
    {
        // TODO: Implement getAuthorizationUrl() method.
    }

    /**
     * Retrieve an access token for a given code
     *
     * @param Request $request The request object where is going to extract the code from
     * @param string $redirectUri The uri to redirect the client back to
     * @param array $extraParameters An array of parameters to add to the url
     *
     * @return array The access token
     */
    public function getAccessToken(Request $request, $redirectUri, array $extraParameters = array())
    {
        // TODO: Implement getAccessToken() method.
    }

    /**
     * Check whatever CSRF token from request is valid or not
     *
     * @param string $csrfToken
     *
     * @return boolean True if CSRF token is valid
     *
     * @throws AuthenticationException When token is not valid
     */
    public function isCsrfTokenValid($csrfToken)
    {
        // TODO: Implement isCsrfTokenValid() method.
    }

    /**
     * Return a name for the resource owner.
     *
     * @return string
     */
    public function getName()
    {
        return $this->resourceOwnerName;
    }

    /**
     * Retrieve an option by name
     *
     * @param string $name The option name
     *
     * @return mixed The option value
     *
     * @throws \InvalidArgumentException When the option does not exist
     */
    public function getOption($name)
    {
        if (!array_key_exists($name, $this->resourceOwnerOptions)) {
            throw new \InvalidArgumentException('the option name [' . $name . '] does not exists.');
        }

        return $this->resourceOwnerOptions[$name];
    }

    /**
     * Checks whether the class can handle the request.
     *
     * @param Request $request
     *
     * @return boolean
     */
    public function handles(Request $request)
    {
        // TODO: Implement handles() method.
    }

    /**
     * Sets a name for the resource owner.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->resourceOwnerName = $name;
    }
}