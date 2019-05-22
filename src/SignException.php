<?php
namespace Yjtec\Sign;
use Exception;
class SignException extends Exception {
    public function __construct($code,$extra=[]){
        $this->code = $code;
        $this->extra = $extra;
    }

    public function getExtra(){
        return $this->extra;
    }
}