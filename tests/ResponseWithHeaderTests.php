<?php

use Ds\Map;
use Ds\Vector;
use Frankie\Response\Response;
use PHPUnit\Framework\TestCase;

class ResponseWithHeaderTests extends TestCase
{
    public function test1()
    {
        $response = new Response();
        $response->withHeader('Content-Type', 'text/plain');
        $expectedValue = new Map();
        $expectedValue['Content-Type'] = new Vector();
        $expectedValue['Content-Type']->push('text/plain');
        $this->assertEquals($expectedValue, $response->getHeaders());
    }

    public function test2()
    {
        $response = new Response();
        $response->withHeader('Content-Type', ['text/plain', 'charset/UTF-8']);
        $expectedValue = new Map();
        $expectedValue['Content-Type'] = new Vector();
        $expectedValue['Content-Type']->push('text/plain');
        $expectedValue['Content-Type']->push('charset/UTF-8');
        $this->assertEquals($expectedValue, $response->getHeaders());
    }

    public function test3()
    {
        $response = new Response();
        $response->withHeader('Content-Type', 'text/plain');
        $response->withHeader('WWW-Authenticate', 'Negotiate');
        $expectedValue = new Map();
        $expectedValue['Content-Type'] = new Vector();
        $expectedValue['Content-Type']->push('text/plain');
        $expectedValue['WWW-Authenticate'] = new Vector();
        $expectedValue['WWW-Authenticate']->push('Negotiate');
        $this->assertEquals($expectedValue, $response->getHeaders());
    }

    public function test4()
    {
        $response = new Response();
        $response->withHeader('Content-Type', ['text/plain', 'charset/UTF-8']);
        $response->withHeader('WWW-Authenticate', 'Negotiate');
        $expectedValue = new Map();
        $expectedValue['Content-Type'] = new Vector();
        $expectedValue['Content-Type']->push('text/plain');
        $expectedValue['Content-Type']->push('charset/UTF-8');
        $expectedValue['WWW-Authenticate'] = new Vector();
        $expectedValue['WWW-Authenticate']->push('Negotiate');
        $this->assertEquals($expectedValue, $response->getHeaders());
    }

    public function test5()
    {
        $response = new Response();
        $response->withHeader('Content-Type', 'text/plain');
        $response->withHeader('WWW-Authenticate', 'Negotiate');
        $response->withHeader('content-type', 'application/json', true);
        $expectedValue = new Map();
        $expectedValue['WWW-Authenticate'] = new Vector();
        $expectedValue['WWW-Authenticate']->push('Negotiate');
        $expectedValue['content-type'] = new Vector();
        $expectedValue['content-type']->push('application/json');
        $this->assertEquals($expectedValue, $response->getHeaders());
    }

    public function test6()
    {
        $response = new Response();
        $response->withHeader('Content-Type', 'text/plain');
        $response->withHeader('WWW-Authenticate', 'Negotiate');
        $response->withHeader('content-type', 'application/json');
        $expectedValue = new Map();
        $expectedValue['Content-Type'] = new Vector();
        $expectedValue['Content-Type']->push('application/json');
        $expectedValue['WWW-Authenticate'] = new Vector();
        $expectedValue['WWW-Authenticate']->push('Negotiate');
        $this->assertEquals($expectedValue, $response->getHeaders());
    }
}
