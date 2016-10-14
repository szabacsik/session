<?php

ini_set ( 'display_errors', -1 );

class session
{
    public function __construct ()
    {
        session_set_cookie_params ( 5 );
        if ( session_status () === PHP_SESSION_ACTIVE )
        {
            if ( !isset ( $_SESSION [ 'id' ] ) || $_SESSION [ 'id' ] !== session_id () )
            {
                $this->start();
            }
        }
        elseif ( session_status () === PHP_SESSION_NONE )
        {
            session_start ();
            $this->start();
        }
        else die ( "Can't start session!" );
    }

    private function start ()
    {
        $_SESSION = array ();
        $_SESSION ['id'] = session_id ();
        $_SESSION ['start'] = microtime ();
    }

    public function __destruct ()
    {
#        session_commit ();
#        session_destroy ();
    }

    public function get ( $key )
    {
        return $_SESSION [ $key ];
    }
}

$session = new session ();

print $session -> get ( 'id' );
print '<br>';
print $session -> get ( 'start' );
print '<br>';
print_r($_SESSION);