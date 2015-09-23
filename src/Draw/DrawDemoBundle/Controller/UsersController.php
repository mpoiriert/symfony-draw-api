<?php

namespace Draw\DrawDemoBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Draw\DrawDemoBundle\Entity\User;
use Draw\DrawBundle\Controller\DoctrineControllerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations as Rest;
use Draw\Swagger\Schema as Swagger;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Validator\Constraints as Assert;

class UsersController extends FOSRestController
{
    use DoctrineControllerTrait;

    /**
     * Create a user
     *
     * @Swagger\Tag(name="Users")
     *
     * @Rest\Post("/users", name="create_user")
     * @Rest\View(serializerGroups={"user:read"})
     *
     * @ParamConverter(
     *      "user",
     *      converter="fos_rest.request_body",
     *      options={
     *        "validator"={"groups"={"user:create"}},
     *        "deserializationContext"={"groups"={"user:create"}}
     *      }
     * )
     *
     * @return \Draw\DrawDemoBundle\Entity\User
     */
    public function createAction(User $user)
    {
        return $this->persistAndFlush($user);
    }

    /**
     * Get a user by it's id
     *
     * @Swagger\Tag(name="Users")
     *
     * @Rest\Get("/users/{id}", name="get_user")
     * @Rest\View(serializerGroups={"user:read"})
     *
     * @ParamConverter("user")
     *
     * @return \Draw\DrawDemoBundle\Entity\User
     */
    public function getAction(User $user)
    {
        return $user;
    }

    /**
     * Allow to edit you information
     *
     * @Swagger\Tag(name="Users")
     *
     * @Rest\Put("/users/{id}", name="edit_user")
     * @Rest\View(serializerGroups={"user:read"})
     *
     * @ParamConverter(
     *      "user",
     *      converter="fos_rest.request_body",
     *      options={
     *        "propertiesMap"={"id":"id"},
     *        "validator"={"groups"={"user:write"}},
     *        "deserializationContext"={"groups"={"user:write"}}
     *      }
     * )
     *
     * @Security("is_granted('OWN', user)")
     *
     * @return \Draw\DrawDemoBundle\Entity\User
     */
    public function editAction(User $user)
    {
        return $this->flush($user);
    }

    /**
     * Find use by their username.
     *
     * The search will match username that start with the username.
     * Result are limited to 50.
     * If you set the exact match parameter to true you will have 0 or 1 result and the username will be
     * exactly your input. This need to be call before the save of username to validate if it's available.
     *
     * @Swagger\Tag(name="Users")
     *
     * @Rest\View(serializerGroups={"user:read"})
     *
     * @Rest\Get("/users", name="list_users")
     * @Rest\QueryParam(name="username", requirements=@Assert\Length(min=3,max=40), nullable=false)
     * @Rest\QueryParam(name="exactMatch", nullable=true, requirements=@Assert\Type("boolean"))
     *
     * @param string $username The user username you are looking for
     * @param boolean $exactMatch If you want to find a username by it's exact match
     *
     * @return \Draw\DrawDemoBundle\Entity\User[]
     */
    public function listAction($username, $exactMatch = false)
    {
        $usernameParameter = $exactMatch ? $username : $username . '%';
        $operator = $exactMatch ? '=' : 'like';

        return $this->getDoctrine()
            ->getRepository("DrawDemoBundle:User")
            ->createQueryBuilder("user")
            ->where("user.username $operator :username")
            ->setParameter("username", $usernameParameter)
            ->getQuery()
            ->setMaxResults($exactMatch ? 1 : 50)
            ->getResult();
    }
}