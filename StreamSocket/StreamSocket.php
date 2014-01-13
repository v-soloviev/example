<?php

/**
 *  StreamSocket - PHP + AJAX Commet Server
 *
 *  @author Valentin Soloviev <info@v-soloviev.ru>
 *  @link http://v-soloviev.ru
 *  @copyright Copyright (c) 2013 Soloviev Valentin
 *  @version 1.0 (16/10/2013)
 */


require(__DIR__ . '/libs/StreamSocketAbstract.php');

class StreamSocket {
    public static $server;
    public static $channels = array();
        
    /**
     * Create the Server Manager
     *
     * @param array|null $options
     * @return Stream server object
     */
    public static function factory(array $options = null){
        # normalize options
        $options = array_merge(array(
            'stream' => 'file',
        ), (array) $options);
        
        self::$channels = array();
        
        if(empty($options['stream']))
            throw new StreamSocketException("{$class} not found.");
        
        $class = 'StreamSocket' . ucfirst($options['stream']);
        if(file_exists(__DIR__ . "/libs/{$class}.php"))
            require(__DIR__ . "/libs/{$class}.php");
        
        if(! class_exists($class))
            throw new StreamSocketException("{$class} not found.");
        
        self::$server = new $class();
        self::$server->init();
        
        return self::$server;
    }
    
    /**
     *  Get model server
     *
     *  @return Stream server object
     */
    public static function server(){
        if(is_null(self::$server))
            throw new StreamSocketException("The server is not initialized, you must first call StreamSocket::factory()");
        
        return self::$server;
    }
    
    /**
     *  Add a channel to listen for
     *
     *  @var (string) name - The channel name
     */
    public static function addChannel($name){
        if(! self::hasChannel($name))
            self::$channels[] = $name;
    }
    
    /**
     *  Is there a channel
     *
     *  @var (string) name - The channel name
     */
    public static function hasChannel($name){
        return in_array($name, self::$channels);
    }
    
    /**
     *  Delete channel
     *
     *  @var (string) name - The channel name
     */
    public static function deleteChannel($name){
        $pos = array_search($name, self::$channels);
        if($pos !== false)
            unset(self::$channels[$pos]);
    }
    
    /**
     *
     *
     */
    public static function channelsAsJsArray(){
        $code = '[';
        
        $channels = array_map(function($c){
            return "'{$c}'";
        }, self::$channels);
        
        $code .= implode(',', $channels);
        
        $code .= ']';
        
        return $code;
    }
    
    private function __construct(){
    }
}

/**
 * StreamSocket exception class.
 */
class StreamSocketException extends Exception {
}
