<?php

spl_autoload_register(function($className)
{
    if(substr($className,0,2) === "yn") {
        $namespace=str_replace("\\","/",__NAMESPACE__);
        $className=str_replace("\\","/",$className);
        $class=__DIR__ . "/" . (empty($namespace)?"":$namespace."/")."{$className}.php";
        include_once($class);
    }
});
