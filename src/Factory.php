<?php

namespace Clue\React\Soap;

use React\EventLoop\LoopInterface;
use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use Clue\React\Soap\Client;
use Clue\React\Soap\ClientStreaming;

use Prewk\XmlStringStreamer\Parser\ParserPipelinesEventDriven;

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

    public function createClient($wsdl, $streaming = false, ParserPipelinesEventDriven $parser_pipelines = null)
    {
        $that = $this;

        return $this->browser->get($wsdl)->then(function (ResponseInterface $response) use ($that, $streaming, $parser_pipelines) {
            return $that->createClientFromWsdl((string)$response->getBody(), $streaming, $parser_pipelines);
        });
    }

    public function createClientFromWsdl($wsdlContents, $streaming = false, ParserPipelinesEventDriven $parser_pipelines = null)
    {
        $browser = $this->browser;
        $loop = $this->loop;
        $url     = 'data://text/plain;base64,' . base64_encode($wsdlContents);

        return !$streaming ? new Client($url, $browser) : new ClientStreaming($url, $browser, null, null, $parser_pipelines, $loop);
    }
}
