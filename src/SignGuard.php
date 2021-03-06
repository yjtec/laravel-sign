<?php 
namespace Yjtec\Sign;
use Illuminate\Auth\TokenGuard;

class SignGuard extends TokenGuard {
    public function __construct($provider,$request){
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = 'appId';
        $this->storageKey = 'app_id';
    }
    public function user(){
        if (! is_null($this->user)) {
            return $this->user;
        }
        $user = null;

        $token = $this->getTokenForRequest();
        if (! empty($token)) {
            $user = $this->provider->retrieveByCredentials(
                [$this->storageKey => $token]
            );
        }
        return $this->user = $user;
    }

    public function getTokenForRequest()
    {
        $token = $this->request->query($this->inputKey);

        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }

        if (empty($token)) {
            $token = $this->request->header($this->inputKey);
        }

        return $token;
    }    

    public function validate(array $credentials = []){
        return false;
    }
}
?>