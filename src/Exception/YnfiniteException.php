<?php

namespace App\Exception;

/* @TODO: Seperate errors from API and errors from PHP into two seperate Exception classes using interfaces */

class YnfiniteException extends \Exception
{
    private $templates;
    private $data;
    private $redirect = null;
    private $renderType = "error";

    public function __construct($message, $code = 0, $pipeBody = false, $path = "/", \Exception $previous = null)
    {
        $finalMessage = "";

        $message = json_decode($message, true);

        if($code === 301) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: ".$message["message"]["fallback"].$path);
            die();
        }

        if (is_array($message["message"]) && $message["message"]["templates"] && $message["message"]["data"]) {
            $this->renderType = "template";
            $this->data = $message["message"]["data"];
            $this->templates = $message["message"]["templates"];
        }
        if (is_array($message["message"]) && $message["message"]["redirect"]) {
            $this->renderType = "template";
            $this->redirect = $message["message"]["redirect"];
        }

        $finalMessage = $this->__toString($message);

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