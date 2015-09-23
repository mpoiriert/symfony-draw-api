<?php

namespace Draw\DrawDemoBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Draw\Swagger\Schema as Swagger;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Validator\Constraints as Assert;

class TokensController extends FOSRestController
{
    /**
     * Create a access token needed for all secured call
     *
     * @Swagger\Tag(name="Tokens")
     *
     * @Rest\Post("/tokens", name="create_tokens")
     * @Rest\RequestParam(name="providerName", default="draw_demo", requirements=@Assert\Choice({"facebook","draw_demo"}))
     * @Rest\RequestParam(name="accessToken")
     * @Rest\View(statusCode=201)
     *
     * @param string $providerName
     * @param array $accessToken The access token relative to the provider
     *
     * @return array
     */
    public function createAction($providerName, $accessToken)
    {
        $provider = $this->get('hwi_oauth.resource_owner.' . $providerName);
        /* @var \HWI\Bundle\OAuthBundle\OAuth\ResourceOwnerInterface $provider */

        $oAuthUserResponse = $provider->getUserInformation(array('access_token' => $accessToken));

        $user = $this->get("draw_demo.user_provider")->loadUserByOAuthUserResponse($oAuthUserResponse);

        $eventDispatcher = $this->get("event_dispatcher");

        $eventDispatcher->addListener(
            Events::JWT_CREATED,
            function (JWTCreatedEvent $event) use ($providerName, $user) {
                $data = $event->getData();
                $data['providerName'] = $providerName;
                $data['username'] = $user->getUsername();
                $event->setData($data);
            }
        );

        $jwt = $this->get('lexik_jwt_authentication.jwt_manager')->create($user);

        $eventDispatcher->dispatch(
            Events::AUTHENTICATION_SUCCESS,
            $event = new AuthenticationSuccessEvent(
                array('token' => $jwt),
                $user,
                $this->container->get('request_stack')->getCurrentRequest()
            )
        );

        return $event->getData();
    }
}