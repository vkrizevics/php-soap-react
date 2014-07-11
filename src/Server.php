<?php

namespace Clue\React\Soap;

class Server
{
    public function handleConnection(Connection $connection)
    {
        // buffer, then handleRequest();

        $this->handleRequest($request)->then(function ($response) use ($connection) {
            $connection->end($response);
        });
    }

    public function handleRequest($request)
    {
        $parsed = $this->parseRequest($request);

        try {
            $promise = $this->callMethod($parsed[0], $parsed[1]);
            if (!$promise instanceof Promise) {
                $ret = new Deferred();
                $ret->resolve($promise);
                $promise = $ret->promise();
            }
        } catch (Exception $e) {
            $ret = new Deferred();
            $ret->reject($e);
            $promise = $ret->promise();
        }

        return $promise->then(
            array($this, 'handleResult'),
            array($this, 'handleError')
        );
    }

    private function callMethod($name, $args)
    {

    }

    private function parseRequest($request)
    {
        $handler = new DecodingPseudoServer();

        $handler->handle($request);

        return array($handler->getMethod(), $handler->getArgs());
    }

    public function handleResult($result)
    {
        $handler = new EncodingPseudoServer($result);

        // handle a pseudo request to inject result value
        $handler->handle('');

        return $handler->getResponse();
    }

    public function handleError(Exception $e) {

    }

    public function addFunction($name, /*callable*/ $callback)
    {

    }
}
