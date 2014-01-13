<?php

class StreamSocketFile extends StreamSocketAbstract {
    
    public function init(){
        parent::init();
        
        $dir = $this->_getFullPath();
        
        if(! is_dir($dir))
            mkdir($dir, 0775);
        
        $files = scandir($dir);
        $files = array_slice($files, 2);
        
        foreach($files as $file){
            $last_mode = filemtime($dir . DIRECTORY_SEPARATOR . $file);
            if($last_mode + $this->timeout < $this->cursor)
                @unlink($dir . DIRECTORY_SEPARATOR . $file);
        }
    }
    
    protected function read($channel){
        $output = array();
        
        $files = glob($this->_getFullPath() . DIRECTORY_SEPARATOR . $this->_getUniqName($channel) . '_*.txt', GLOB_NOSORT);
        foreach($files as $file){
            $data = file_get_contents($file);
            $data = explode('|', $data);
            
            $output[] = json_decode($data[0], true);
            
            @unlink($file);
        }
        
        if(! count($output))
            return $this->nobody();
        
        return $output;
    }
    
    protected function write($channel, $data){        
        $data = implode('|', array($data, microtime(true))) . "\n";
        
        return file_put_contents($this->_getFullPath() . DIRECTORY_SEPARATOR . $this->_getFileName($channel), $data, FILE_APPEND);
    }
    
    private function _getUniqName($channel){
        return md5("__StreamSocket:{$channel}");
    }
    
    private function _getFileName($channel){
        return $this->_getUniqName($channel) . '_' . uniqid('', true) . '_' . '.txt';
    }
    
    private function _getFullPath(){
        return realpath(__DIR__ . '/../channels/');
    }
}
