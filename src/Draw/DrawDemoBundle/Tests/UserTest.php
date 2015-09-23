<?php

namespace Draw\DrawDemoBundle\Tests;

use Draw\DrawBundle\Test\WebTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    use WebTestCaseTrait;

    public function testCreateUser()
    {
        return $this->requestHelper()
            ->post('/api/draw-demo/users')
            ->asJson()
            ->withBody(
                array(
                    'username' => 'Username',
                    'newPassword' => '123456'
                )
            )
            ->propertyHelper('id')->isOfType('integer')->replace()->attach()
            ->executeAndDecodeJson();
    }

    /**
     * @depends testCreateUser
     *
     * @param array $user
     * @return mixed
     */
    public function testGetUser($user)
    {
        return $this->requestHelper()
            ->get('/api/draw-demo/users/' . $user['id'])
            ->asJson()
            ->propertyHelper('id')->isSameAs($user['id'])->replace()->attach()
            ->executeAndDecodeJson();
    }
}