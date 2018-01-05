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
use React\Stream\Stream as ReactPhpStream;
use React\EventLoop\Looplnterface;

class ClientStreaming extends Client implements EventEmitterInterface
{
    use EventEmitterTrait;
    
    protected $parser_pipelines = null;

    public function __construct($wsdl, Browser $browser, ClientEncoder $encoder = null, ClientDecoder $decoder = null, ParserPipelinesEventDriven $parser_pipelines = null, \React\EventLoop\LoopInterface $loop = null)
    {
        parent::__construct($wsdl, $browser->withOptions(array('streaming' => true)), $encoder, $decoder);
        $this->parser_pipelines = !$parser_pipelines
	    ? ParserPipelinesEventDriven::getInstance()
	    : $parser_pipelines;
        $this->loop = $loop;
    }

    public function soapCall($name, $args)
    {
	$that = $this;
        $stream = Stream\unwrapReadable(parent::soapCall($name, $args))
	    ->on('data', function($chunk) use ($that) {
           $that->emit('data', array($chunk));

		$results = $that->parser_pipelines->apply($chunk);
		array_walk($results, function($res, $event_name) use ($that) {
       if (!is_array($res)) {
           $res = @simple_xml_loadstring($res);
       }
		   $that->emit($event_name, array($res));
		});
	    })
	    ->on('error', function(\Exception $e) use ($that, $stream) 
{
    $that->emit('error', array($e));
})
	    ->on('end', function($result) use ($that, $stream)
{
    $that->emit('end', array($result));
});
     
         	return $this;
    }

    public function handleResponse(ResponseInterface $response)
    {   
        return $response->getBody();
    }
}
