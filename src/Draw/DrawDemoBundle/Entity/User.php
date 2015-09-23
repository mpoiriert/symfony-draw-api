<?php

namespace Draw\DrawDemoBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Draw\DrawBundle\Security\OwnedInterface;
use Draw\DrawBundle\Security\OwnerInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="look_like_user")
 * @UniqueEntity("username")
 * @ORM\HasLifecycleCallbacks()
 */
class User implements UserInterface, OwnedInterface, OwnerInterface
{
    use Timestampable;

    /**
     * @var integer
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(name="id", type="integer")
     *
     * @Assert\NotNull()
     *
     * @Serializer\ReadOnly()
     * @Serializer\Groups({"user:read", "user:property:id"})
     */
    public $id;

    /**
     * Unique username of the user in the system
     *
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=40, nullable=true, unique=true)
     *
     * @Assert\Length(min=3, max=40, groups={"persist"})
     *
     * @Serializer\Groups(groups={"user:read", "user:create"})
     */
    public $username;

    /**
     * The encoded password of the user
     *
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     *
     * @Assert\Length(min=1, max=255, groups={"persist"})
     * @Assert\NotNull(groups={"persist"})
     * @Assert\Type(type="string", groups={"persist"})
     *
     * @Serializer\Exclude()
     */
    public $password;

    /**
     * The clear new password of the user
     *
     * @var string
     *
     * @Assert\Length(min=3, max=40, groups={"user:write"})
     * @Assert\Type(type="string", groups={"user:write"})
     *
     * @Serializer\Type("string")
     * @Serializer\Groups(groups={"user:write"})
     */
    public $newPassword;

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles()
    {
        return array(
            'ROLE_USER'
        );
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getOwnerId()
    {
        return $this->id;
    }

    /**
     * Return if the object is owned by the possible owner
     *
     * @param OwnerInterface $owner
     * @return boolean
     */
    public function isOwnedBy(OwnerInterface $possibleOwner)
    {
        return $this->id == $possibleOwner->getOwnerId();
    }

    /**
     * Returns a $length random string
     * @param int $length
     * @return string
     */
    public static function generateRandomPassword($length = 8)
    {
        $characters = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }


    /**
     * Pre persist hook to set password if null.
     *
     * @ORM\PrePersist()
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        if(is_null($entity->password)) {
            $entity->password = static::generateRandomPassword();
        }
    }
}