<?php
namespace Clue\React\Soap;

use Clue\React\Buzz\Browser;
use Clue\React\Soap\Client;
use Clue\React\Soap\ClientEncoder;
use Clue\React\Soap\ClientDecoder;
use Psr\Http\Message\ResponseInterface;
use React\Promise\Stream;
use Prewk\XmlStringStreamer\Parser\ParserPipelinesEventDriven;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;

class ClientStreaming extends Client implements EventEmitterInterface
{
    use EventEmitterTrait;
    
    protected $parser_pipelines = null;

    public function __construct($wsdl, Browser $browser, ClientEncoder $encoder = null, ClientDecoder $decoder = null, ParserPipelinesEventDriven $parser_pipelines = null)
    {
        parent::__construct($wsdl, $browser, $encoder, $decoder);
        $this->parser_pipelines = !$parser_pipelines
	    ? ParserPipelinesEventDriven::getInstance(array($this, 'emit'))
	    : $parser_pipelines->setEventCallback(array($this, 'emit'));
	print_r($this->parser_pipelines);
    }

    public function soapCall($name, $args)
    {
        $this->stream = Stream\unwrapReadable(parent::soapCall($name, $args));
	    //->on('data', function($chunk) { echo $chunk;}/*array($this, 'emit')*/);
            //->on('data', array($this->parsers_pipelines, 'applyParsers'))
            //->on('error', array($this, 'emit'))
	    //->on('end', array($this, 'emit'));
         
	return $this->stream;
    }

    public function handleResponse(ResponseInterface $response)
    {
        return $response->getBody();
    }
}
