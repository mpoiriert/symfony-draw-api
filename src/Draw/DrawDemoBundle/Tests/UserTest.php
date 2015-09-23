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
            ->validateAgainstFile()
            ->expectingStatusCode(201)
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
            ->validateAgainstFile()
            ->executeAndDecodeJson();
    }

    /**
     * @depends testCreateUser
     *
     * @param array $user
     * @return mixed
     */
    public function testCreateToken()
    {
        $username = 'Username';
        $password = '123456';

        return $this->requestHelper()
            ->post('/api/draw-demo/tokens')
            ->withBody(['accessToken' => json_encode(compact("username", "password"))])
            ->asJson()
            ->propertyHelper('token')->attach()
            ->expectingStatusCode(201)
            ->executeAndDecodeJson();
    }

    /**
     * @depends testCreateToken
     * @depends testGetUser
     *
     * @param $token
     * @param $user
     */
    public function testEditUser($token, $user)
    {
        $this->connect($token['token']);

        return $this->requestHelper()
            ->put('/api/draw-demo/users/' . $user['id'])
            ->withBody(['username' => 'NewUsername'])
            ->asJson()
            ->propertyHelper('id')->isSameAs($user['id'])->replace()->attach()
            ->propertyHelper('username')->isSameAs('NewUsername')->attach()
            ->executeAndDecodeJson();
    }

    private function connect($token)
    {
        static::$client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));
    }
}