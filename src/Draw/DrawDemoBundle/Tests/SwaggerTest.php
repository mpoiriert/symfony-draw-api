<?php

namespace Draw\DrawDemoBundle\Tests;

use Draw\DrawBundle\Test\WebTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SwaggerTest extends WebTestCase
{
    use WebTestCaseTrait;

    public function testSwagger()
    {
        $this->requestHelper()
            ->get('/api-doc')
            ->asJson()
            ->validateAgainstFile()
            ->executeAndDecodeJson();
    }
}