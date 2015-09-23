<?php

namespace Draw\DrawDemoBundle\User;

use HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

class UserResponse implements UserResponseInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var ResourceOwnerInterface
     */
    private $resourceOwner;

    public function __construct($username)
    {
        $this->username = $username;
    }


    /**
     * Get the api response.
     *
     * @return array
     */
    public function getResponse()
    {
        // TODO: Implement getResponse() method.
    }

    /**
     * Set the raw api response.
     *
     * @param string|array $response
     */
    public function setResponse($response)
    {
        // TODO: Implement setResponse() method.
    }

    /**
     * Get the resource owner responsible for the response.
     *
     * @return ResourceOwnerInterface
     */
    public function getResourceOwner()
    {
        return $this->resourceOwner;
    }

    /**
     * Set the resource owner for the response.
     *
     * @param ResourceOwnerInterface $resourceOwner
     */
    public function setResourceOwner(ResourceOwnerInterface $resourceOwner)
    {
        $this->resourceOwner = $resourceOwner;
    }

    /**
     * Get the unique user identifier.
     *
     * Note that this is not always common known "username" because of implementation
     * in Symfony2 framework. For more details follow link below.
     * @link https://github.com/symfony/symfony/blob/2.1/src/Symfony/Component/Security/Core/User/UserProviderInterface.php#L20-L28
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the username to display.
     *
     * @return string
     */
    public function getNickname()
    {
        // TODO: Implement getNickname() method.
    }

    /**
     * Get the first name of user.
     *
     * @return null|string
     */
    public function getFirstName()
    {
        // TODO: Implement getFirstName() method.
    }

    /**
     * Get the last name of user.
     *
     * @return null|string
     */
    public function getLastName()
    {
        // TODO: Implement getLastName() method.
    }

    /**
     * Get the real name of user.
     *
     * @return null|string
     */
    public function getRealName()
    {
        // TODO: Implement getRealName() method.
    }

    /**
     * Get the email address.
     *
     * @return null|string
     */
    public function getEmail()
    {
        // TODO: Implement getEmail() method.
    }

    /**
     * Get the url to the profile picture.
     *
     * @return null|string
     */
    public function getProfilePicture()
    {
        // TODO: Implement getProfilePicture() method.
    }

    /**
     * Get the access token used for the request.
     *
     * @return string
     */
    public function getAccessToken()
    {
        // TODO: Implement getAccessToken() method.
    }

    /**
     * Get the access token used for the request.
     *
     * @return null|string
     */
    public function getRefreshToken()
    {
        // TODO: Implement getRefreshToken() method.
    }

    /**
     * Get oauth token secret used for the request.
     *
     * @return null|string
     */
    public function getTokenSecret()
    {
        // TODO: Implement getTokenSecret() method.
    }

    /**
     * Get the info when token will expire.
     *
     * @return null|string
     */
    public function getExpiresIn()
    {
        // TODO: Implement getExpiresIn() method.
    }

    /**
     * Set the raw token data from the request.
     *
     * @param OAuthToken $token
     */
    public function setOAuthToken(OAuthToken $token)
    {
        // TODO: Implement setOAuthToken() method.
    }
}