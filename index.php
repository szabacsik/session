<?php

class session
{
    public function __construct ()
    {
    	session_start ();
    }

    public function __destruct ()
    {
    }
}

$session = new session ();

?>
