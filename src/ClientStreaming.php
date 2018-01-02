<?php
namespace Clue\React\Soap;


use Clue\React\Buzz\Browser;
use Clue\React\Soap\Client;
use Psr\Http\Message\ResponseInterface;
use React\Promise\Stream;

class ClientStreaming extends Client implements EventEmitterInterface
{
    use EventEmitterTrait;

    public function __construct($wsdl, Browser $browser, ClientEncoder $encoder = null, ClientDecoder $decoder = null, ParsersPipelinesEventDriven $parsers_pipelines = null)
    {
        parent::__construct($wsdl, $browser, $encoder, $decoder);
        $this->parsers_pipelines = !$parsers_pipelines ? ParsersPipelinesEventDriven::getInstance(($this, 'emit')) : $parsers_pipelines;
    }

    public function soapCall($name, $args)
    {
        $this->stream = Stream\unwrapReadable(parent::soapCall($name, $args))->on('data', ($this, 'emit'))->on('data',($this->parsers_pipelines, 'applyParsers'))->on('error', ($this, 'emit'))->on('end', ($this, 'emit'));
         return $this;
    }

    public function handleResponse(ResponseInterface $response)
    {
        return $response->getBody();
    }
}
