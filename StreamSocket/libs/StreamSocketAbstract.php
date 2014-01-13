<?php

abstract class StreamSocketAbstract {
    protected $timeout = 25;
    protected $cursor;
    
    public function init(){
        $this->cursor = microtime(true);
    }
    
    /**
     *  Send the data to the list of channels
     *  
     *  @var (array) channels - Channel list
     *  @var (array) data - An associative array of data
     *
     */
    public function send($channels, $data){
        $data = json_encode($data);
        foreach((array) $channels as $channel)
            $this->write($channel, $data);
    }
    
    /**
     *  Get data from the channel
     *  
     *  @var (string) channel - The channel name
     *  @return (array) output - Data
     */
    protected function get($channel){
        $output = $this->read($channel);
        if(! is_array($output))
            $output = array($output);
            
        return $output;
    }
    
    /**
     *  Listen channels
     *  
     *  @var (array) channels - channel list
     *  @var (booled) $return - return or echo resulr
     *  @return response
     */
    public function comet($channels = array(), $return = false){
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        if(! isset($_SERVER['HTTP_X_STREAMSOCKET']) || ! $isAjax)
            throw new StreamSocketException('Invalid HTTP request');
        
        ignore_user_abort(true);
        set_time_limit($this->timeout + 5);
        
        $time = time();
        while (time() - $time < $this->timeout) {
            $response = array();
            foreach((array) $channels as $channel){
                $data = $this->get($channel);
                if(count($data))
                    $response[$channel] = $data;
            }
            
            if(count($response))
                break;
            
            $response = $this->nobody();
            
            sleep(5);
        }
        
        if($return)
            return json_encode($response);
        else
            echo json_encode($response);
    }
    
    /**
     *  Default The default answer in the case of empty data
     *  
     *  @return default response
     */
    protected function nobody(){
        return array();
    }
    
    /**
     *  Read the data from the channel
     *
     *  @var (string) channel - The channel name
     *  abstract method
     */
    abstract protected function read($channel);
    
    /**
     *  Write the data into the channel
     *
     *  @var (array) channels - Channel list
     *  @var (array) data - JSON data
     *  abstract method
     */
    abstract protected function write($channel, $data);
}
