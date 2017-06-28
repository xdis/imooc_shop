<?php

class Test
{
    function callback ($msg)
    {
        echo $msg;
    }

    function show ($object, $callback)
    {
        $object->$callback('this is callback');
    }
}


$object = new Test();
$object->show($object, 'callback');

