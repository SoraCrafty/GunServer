<?php

namespace gun\discord;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class AsyncSendTask extends AsyncTask
{

    private $message;
    private $webhook;
    private $response;

    public function __construct($message, $webhook)
    {
        $this->message = $message;
        $this->webhook = $webhook;
    }

    public function onRun()
    {
        $options = [
                'http' => [
                              'method' => 'POST',
                              'header' => 'Content-Type: application/json',
                              'content' => json_encode($this->message),
                        ]
                    ];
        $this->response = file_get_contents($this->webhook, false, stream_context_create($options));
    }

    public function onCompletion(Server $server)
    {
        //var_dump($this->response);
    }
}