<?php

use Ds\Sequence;
use Frankie\Response\Response;
use PHPUnit\Framework\TestCase;

class ResponseTests extends TestCase
{
    /**
     * @runInSeparateProcess
     * @dataProvider invalidStatusCodes
     */
    public function testInvalidStatusCode($code)
    {
        $this->expectException(InvalidArgumentException::class);
        $response = new Response();
        $response->withStatus($code);
    }

    /**
     * @runInSeparateProcess
     * @dataProvider statusHeaderContent
     */
    public function testStatusHeader($code, $reasonPhrase, $header)
    {
        $response = new Response();
        $response->withStatus($code, $reasonPhrase);
        $this->assertContainsOnly($header, xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     * @dataProvider headerHasContent
     */
    public function testHasHeader($name, $result)
    {
        $response = new Response();
        $response->withHeader('Content-Type', 'text/plain');
        $res = $response->hasHeader($name);
        $this->assertEquals($res, $result);
    }

    /**
     * @runInSeparateProcess
     * @dataProvider statusHeaderContent
     */
    public function testStatusHeaderNullReason()
    {
        $response = new Response();
        $response->withStatus(104);
        $this->assertContainsOnly('Status: 104 unknown status', xdebug_get_headers());
    }

    /**
     * @runInSeparateProcess
     * @dataProvider withoutHeadersContent
     */
    public function testWithoutHeader($name, $result)
    {
        $response = new Response();
        $response->withHeader('WWW-Authenticate', 'Negotiate');
        $headersBefore = $response->getHeaders();
        $response->withHeader('Content-Type', 'text/plain')
            ->withoutHeader($name);
        $headersAfter = $response->getHeaders();
        $this->assertSame($result, $headersAfter == $headersBefore);
    }

    /**
     * *@runInSeparateProcess
     */
    public function testSend()
    {
        $response = new Response();
        $response->withHeader('Content-Type', 'application/json');
        $response->send();
        $this->assertTrue($this->checkSimilar($response->getHeaders(), xdebug_get_headers()));

        $response = new Response();
        $response->withHeader('Content-Type', 'application/json');
        $response->withHeader('WWW-Authenticate', 'Negotiate');
        $response->send();
        $this->assertTrue($this->checkSimilar($response->getHeaders(), xdebug_get_headers()));

        $response = new Response();
        $response->withHeader('Content-Type', ['application/json', 'text/plain']);
        $response->withHeader('WWW-Authenticate', 'Negotiate');
        $response->send();
        $this->assertTrue($this->checkSimilar($response->getHeaders(), xdebug_get_headers()));
    }

    public function invalidStatusCodes()
    {
        return [
            [99],
            [600]
        ];
    }

    public function statusHeaderContent()
    {
        return [
            [404, '', 'Status: 404 Not Found'],
            [203, 'new', 'Status: 203 new'],
            [104, 'new', 'Status: 104 new'],
        ];
    }

    public function headerHasContent()
    {
        return [
            ['Content-Type', true],
            ['content-type', true],
            ['CONTENT-TYPE', true],
            ['charset', false],
        ];
    }

    public function withoutHeadersContent()
    {
        return [
            ['Content-Type', true],
            ['content-type', true],
            ['CONTENT-TYPE', true],
            ['charset', false]
        ];
    }

    private function checkSimilar($responseHeaders, array $sentHeaders)
    {
        if ((count($responseHeaders) + 1) !== count($sentHeaders)) {
            return false;
        }
        /**
         * @var  Sequence $responseHeader
         */
        foreach ($responseHeaders as $name => $responseHeader) {
            $header = $name . ': ' . $responseHeader->join('; ');
            foreach ($sentHeaders as $key => $val) {
                if ($sentHeaders[$key] === $header) {
                    unset($sentHeaders[$key]);
                }
            }
        }
        return count($sentHeaders) === 1;
    }
}
