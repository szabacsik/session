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
        $_SESSION [ 'expiration_timestamp' ] = floatval ( $_SESSION [ 'start_timestamp' ] ) + floatval ( ini_get ( 'session.cookie_lifetime' ) );
        $now = DateTime::createFromFormat ( 'U.u', microtime ( true ) );
        $debug = var_export ( $now, true );
        file_put_contents('session_dump.txt',$debug,FILE_APPEND);
        $expiration = DateTime::createFromFormat ( 'U.u', floatval ( microtime ( true ) ) + floatval ( ini_get ( 'session.cookie_lifetime' ) ) );
        $_SESSION [ 'start_datetime' ] = $now -> format ( "Y-m-d H:i:s.u" );
        $_SESSION [ 'expiration_datetime' ] = $expiration -> format ( "Y-m-d H:i:s.u" );
    }

    public function stop ()
    {
        session_unset ();
        session_destroy ();
    }

    private function active ()
    {
        if ( version_compare ( phpversion (), '5.4.0', '<' ) )
        {
            return isset ( $_SESSION [ 'id' ] ) && $_SESSION [ 'id' ] === session_id ();
        }
        else
        {
            return session_status () === PHP_SESSION_ACTIVE && isset ( $_SESSION [ 'id' ] ) && $_SESSION [ 'id' ] === session_id ();
        }
    }

    private function setup ()
    {
        session_set_cookie_params ( 3600 ); //session.cookie_lifetime
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

    public function add ( $key, $value )
    {
        if ( isset ( $_SESSION [ $key ] ) )
        {
        }
        else
        {
            $_SESSION [ $key ] = array ();
        }
        array_push ( $_SESSION [ $key ], $value );
    }

    public function remove ( $key )
    {
        unset ( $_SESSION [ $key ] );
    }
}
