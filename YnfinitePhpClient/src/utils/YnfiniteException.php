<?php

namespace Ypsolution\YnfinitePhpClient\utils;

/* @TODO: Seperate errors from API and errors from PHP into two seperate Exception classes using interfaces */

class YnfiniteException extends \Exception
{
    private $templates;
    private $data;
    private $redirect = null;
    private $renderType = "error";

    public function __construct($message, $code = 0, $pipeBody = false, \Exception $previous = null)
    {
        $finalMessage = "";

        if($message && is_array($message)) {
            if (is_array($message["message"]) && $message["message"]["templates"] && $message["message"]["dataArray"]) {
                $this->renderType = "template";
                $this->data = $message["message"]["dataArray"];
                $this->templates = $message["message"]["templates"];
            }
            if (is_array($message["message"]) && $message["message"]["redirect"]) {
                $this->renderType = "template";
                $this->redirect = $message["message"]["redirect"];
            }

            $finalMessage = $this->__toString($message);
        }
        else {
            if($pipeBody === true) {
                $this->renderType = "cache-pipe";
            }
            $finalMessage = $message;
        }


        parent::__construct($finalMessage, $code, $previous);
    }


    public function getData()
    {
        return $this->data;
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    public function getRenderType()
    {
        return $this->renderType;
    }

    public function getRedirect()
    {
        return $this->redirect;
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}