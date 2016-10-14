<?php

class session
{
    public function __construct ()
    {
        if ( session_status () == PHP_SESSION_NONE )
        {
            session_set_cookie_params ( 3600 );
            session_start ();
            $_SESSION = array ();
        }
    }

    public function __destruct ()
    {
        session_commit ();
    }

    public function id ()
    {
        return session_id ();
    }
}

$session = new session ();

print $session -> id ();
