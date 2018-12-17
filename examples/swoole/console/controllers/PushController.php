<?php
namespace console\controllers;

use yiiplus\websocket\swoole\Controller;

class PusController extends Controller
{
    public function message($server, $frame)
    {
        foreach ($server->connections as $fd) {
            $server->push($fd, $frame->data);
        }
    }
}
