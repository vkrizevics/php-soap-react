<?php
namespace Clue\React\Soap;


use Clue\React\Buzz\Browser;
use Clue\React\Soap\Client;
use Psr\Http\Message\ResponseInterface;
use React\Promise\Stream;


class ClientStreaming extends Client
{

    public function __construct($wsdl, Browser $browser, ClientEncoder $encoder = null, ClientDecoder $decoder = null)
    {
        parent::__construct($wsdl, $browser, $encoder, $decoder);
    }

    public function soapCall($name, $args)
    {
        return Stream\unwrapReadable(parent::soapCall($name, $args));
    }

    public function handleResponse(ResponseInterface $response)
    {
        return $response->getBody();
    }
}
