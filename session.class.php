<?php

class session
{
    public function __construct ()
    {
        $this -> setup ();
        session_start ();
        if ( !$this -> active () )
        {
            $this -> start ();
        }
    }

    private function start ()
    {
        $_SESSION [ 'id' ] = session_id ();
        $_SESSION [ 'start_timestamp' ] = microtime ( true );
        $_SESSION [ 'expiration_timestamp' ] = $_SESSION [ 'start_timestamp' ] + ini_get ( 'session.cookie_lifetime' );
        $now = DateTime::createFromFormat ( 'U.u', microtime ( true ) );
        $_SESSION [ 'start_datetime' ] = $now -> format ( "Y-m-d H:i:s.u" );
    }

    public function stop ()
    {
        session_unset ();
        session_destroy ();
    }

    private function active ()
    {
        return session_status () === PHP_SESSION_ACTIVE && isset ( $_SESSION [ 'id' ] ) && $_SESSION [ 'id' ] === session_id ();
    }

    private function setup ()
    {
        session_set_cookie_params ( 10 ); //session.cookie_lifetime
        session_name ( 'private' );
    }

    public function __destruct ()
    {
        session_commit ();
    }

    public function get ( $key )
    {
        if ( isset ( $_SESSION [ $key ] ) )
        {
            return $_SESSION [ $key ];
        }
        else
        {
            return false;
        }
    }

    public function set ( $key, $value )
    {
        $_SESSION [ $key ] = $value;
    }
}
