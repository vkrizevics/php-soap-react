<?php

use Clue\React\Soap\Factory;
use Clue\React\Soap\Proxy;
use Clue\React\Soap\Client;
use Clue\React\Soap\ClientStreaming;

use Prewk\XmlStringStreamer\Parser\UniqueNodeEventDriven;
use Prewk\XmlStringStreamer\Parser\StringWalkerEventDriven;
use Prewk\XmlStringStreamer\Parser\ParserPipelinesEventDriven;

require __DIR__ . '/../../../../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$factory = new Factory($loop);



for($i = 0; $i < 5; $i++) :

    $blz = isset($argv[1]) ? $argv[1] : '1207000'. $i;

	$factory->createClient('http://www.thomas-bayer.com/axis2/services/BLZService?wsdl', true, ParserPipelinesEventDriven::getInstance()
		->setParser('details', new UniqueNodeEventDriven(array("uniqueNode" => "ns1:details")))
		->setParser('plz', new UniqueNodeEventDriven(array("uniqueNode" => "ns1:plz")))
		->setParser('children', new StringWalkerEventDriven(array("captureDepth" => 2)))
		->setParser('details_elem', new StringWalkerEventDriven(array("captureDepth" => 5)))
		//->setParserPipeline('plz', array('details', 'plz'))
		->setParserPipeline('details', array('details', 'children'))
		->setParserPipeline('details_node', array('details'))
		//->setParserPipeline('details_elem', array('details_elem'))
	)->then(function (Clue\React\Soap\ClientStreaming $client) use ($blz, $i, $loop)
	{				
	    $api = new Proxy($client);
	    
	    $client->on('error', function ($e) use($i, $loop) 
		{
		    echo 'ERROR: ' . $i . $e->getMessage() . PHP_EOL;
		})
		//->on('data', function($result){ echo $result;})
		->on('details', function (array $nodes) use ($blz)
		{
		    //echo 'Got '. print_r($nodes, 1) .' for '. $blz .'\r\n';
		})
		->on('details_node', function ($node) use ($blz)
		{
		    echo 'Got '. var_dump($node) .' for '. $blz .'\r\n';
		});
		//->on('plz', 'print_r')
		//->on('details_elem', 'var_dump');
   
	    $api->getBank(array('blz' => $blz));
	});
endfor;

$loop->run();
