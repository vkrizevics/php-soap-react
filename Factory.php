<?php

namespace Clue\React\Soap;

use React\EventLoop\LoopInterface;
use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;

class Factory
{
    private $loop;
    private $browser;

    public function __construct(LoopInterface $loop, Browser $browser = null)
    {
        if ($browser === null) {
            $browser = new Browser($loop);
        }
        $this->loop = $loop;
        $this->browser = $browser;
    }

    public function createClient($wsdl,   $streaming = false)
    {
        $that = $this;

        return $this->browser->get($wsdl)->then(function (ResponseInterface $response) use ($that, $streaming) {
            return $that->createClientFromWsdl((string)$response->getBody(), $streaming);
        });
    }

    public function createClientFromWsdl($wsdlContents, $streaming = false)
    {
        $browser = $this->browser;
        $url     = 'data://text/plain;base64,' . base64_encode($wsdlContents);

        return !$streaming ? new Client($url, $browser) : new ClientStreaming($url, $browser);
    }
}
