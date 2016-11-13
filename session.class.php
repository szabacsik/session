<?php

define ( 'php_session_id', 'sid' );

class session
{
    private $expiration_time;

    public function __construct ( $expiration_time = 3600 )
    {
        $this -> expiration_time = $expiration_time;
        $this -> setup ();
        session_start ();
        if ( !$this -> active () ) $this -> start ();
        $this->debug();
    }

    private function start ()
    {
        //$now = DateTime::createFromFormat ( 'U.u', number_format ( microtime ( true ), 6, '.', '' ) );
        session_regenerate_id ( true );
        $now = microtime ( true );
        $micro = sprintf ( "%06d", ( $now - floor ( $now ) ) * 1000000 );
        $date = new DateTime ( date ( 'Y-m-d H:i:s.' . $micro, $now ) );
        $_SESSION [ php_session_id ] = session_id ();
        $_SESSION [ 'uid' ] = sha1 ( $this -> uuid () );
        $_SESSION [ 'start_datetime' ] = $date -> format ( "Y-m-d H:i:s.u" );
        $_SESSION [ 'start_timestamp' ] = $now;
        $this -> time_extension ();
    }

    public function heartbeat ()
    {
        //$now = DateTime::createFromFormat ( 'U.u', microtime ( true ) );
        //$now = DateTime::createFromFormat ( 'U.u', number_format ( microtime ( true ), 6, '.', '' ) );
        $now = microtime ( true );
        $micro = sprintf ( "%06d", ( $now - floor ( $now ) ) * 1000000 );
        $date = new DateTime ( date ( 'Y-m-d H:i:s.' . $micro, $now ) );
        //$expiration = $now->add(new DateInterval('PT'.ini_get ( 'session.cookie_lifetime' ).'S'));
        //$expiration = DateTime::createFromFormat ( 'U.u', floatval ( microtime ( true ) ) + floatval ( ini_get ( 'session.cookie_lifetime' ) ) );
        //$expiration = DateTime::createFromFormat ( 'U.u', floatval ( number_format ( microtime ( true ), 6, '.', '' ) ) + floatval ( ini_get ( 'session.cookie_lifetime' ) ) );
        $_SESSION [ 'last_activity_timestamp' ] = $now;
        $_SESSION [ 'last_activity_datetime' ] = $date -> format ( "Y-m-d H:i:s.u" );
        if ( !$this -> expired () )
        {
            $this -> time_extension ();
        }
        //$_SESSION [ 'expiration_timestamp' ] = floatval ( $_SESSION [ 'last_activity_timestamp' ] ) + floatval ( ini_get ( 'session.cookie_lifetime' ) );
        //$_SESSION [ 'expiration_datetime' ] = $expiration -> format ( "Y-m-d H:i:s.u" );
    }

    private function time_extension ()
    {
        $now = microtime ( true );
        $expiration = $now + floatval ( $this -> expiration_time );
        $_SESSION [ 'expiration_timestamp' ] = $expiration;
        $micro = sprintf ( "%06d", ( $expiration - floor ( $expiration ) ) * 1000000 );
        $expiration = new DateTime ( date ( 'Y-m-d H:i:s.' . $micro, $expiration ) );
        $_SESSION [ 'expiration_datetime' ] = $expiration -> format ( "Y-m-d H:i:s.u" );
    }
    
    public function expired ()
    {
        return $_SESSION [ 'last_activity_timestamp' ] > $_SESSION [ 'expiration_timestamp' ];
    }

    public function stop ()
    {
        session_reset ();
        session_unset ();
        session_destroy ();
    }

    private function active ()
    {
        if ( version_compare ( phpversion (), '5.4.0', '<' ) )
        {
            return isset ( $_SESSION [ php_session_id ] ) && $_SESSION [ php_session_id ] === session_id ();
        }
        else
        {
            return session_status () === PHP_SESSION_ACTIVE && isset ( $_SESSION [ php_session_id ] ) && $_SESSION [ php_session_id ] === session_id ();
        }
    }

    private function setup ()
    {
        //http://php.net/manual/en/session.configuration.php
        // session.cookie_lifetime ( integer ) specifies the lifetime of the cookie in seconds which is sent to the browser.
        // The value 0 means "until the browser is closed." Defaults to 0.
        // See also session_get_cookie_params() and session_set_cookie_params()
        session_set_cookie_params ( 0 ); //session.cookie_lifetime
        // session.gc_maxlifetime ( integer ) specifies the number of seconds after which data will be seen as 'garbage'
        // and potentially cleaned up. Garbage collection may occur during session start
        // (depending on session.gc_probability and session.gc_divisor).
        ini_set ( 'session.gc_maxlifetime', 86400 );
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
            return null;
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

    public function debug ()
    {
        $debug = var_export ( $_SESSION, true );
        file_put_contents ( 'session_dump.txt', $debug . "\n\n", FILE_APPEND );
    }
    
    public function uuid ()
    {
        // http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
        //return sprintf ( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        //                  // 32 bits for "time_low"
        //                  mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        //                  // 16 bits for "time_mid"
        //                  mt_rand( 0, 0xffff ),
        //                  // 16 bits for "time_hi_and_version",
        //                  // four most significant bits holds version number 4
        //                  mt_rand( 0, 0x0fff ) | 0x4000,
        //                  // 16 bits, 8 bits for "clk_seq_hi_res",
        //                  // 8 bits for "clk_seq_low",
        //                  // two most significant bits holds zero and one for variant DCE1.1
        //                  mt_rand( 0, 0x3fff ) | 0x8000,
        //                  // 48 bits for "node"
        //                  mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        //                );
        // http://security.stackexchange.com/questions/101112/can-i-rely-on-openssl-random-pseudo-bytes-being-very-random-in-php
        $data = openssl_random_pseudo_bytes ( 16 );
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}
