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
    }

    public function soapCall($name, $args)
    {
	$that = $this;
        $this->stream = /*Stream\unwrapReadable*/(parent::soapCall($name, $args));/*
	    ->on('data', function($chunk) use ($that) {$that->emit('data', array($chunk));})
	    ->on('data', function($chunk) use ($that)
	    {
		$results = $this->parsers_pipelines->applyParsers($chunk);
		array_walk($results, function($res, $event_name) use ($that) {
		   $that->emit($event_name, $res);
		});
	    })
	    ->on('error', function($result) use ($that) {$that->emit('error', array($result));})
	    ->on('end', function($result) use ($that) {$that->emit('end', array($result));});
*/         
	return $this;
    }

    public function handleResponse(ResponseInterface $response)
    {
        Stream\unwrapReadable($response->getBody())->on('data', 'print_r')->on('error', 'print_r');
    }
}
