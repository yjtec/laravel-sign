<?php

namespace Yjtec\Sign;
use Closure;
class AuthApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //dd(env('APP_ENV'));
        if (
            in_array(env('APP_ENV'), ['local', 'test']) ||
            $request->has('debug')
        ) {
            return $next($request);
        }
        $param = $request->all();
        $appId = $request->appId;
        $timeStamp = $request->timeStamp;
        $sign      = $request->sign;
        if(!$appId || !$timeStamp || !$sign){
            throw new SignException('NO_PERMISSION');
        }

        $this->timeStamp = $timeStamp;
        $this->secret = 'q1w2e3r4';
        $this->sign = $sign;
        unset($param['sign']);
        $this->checkTime();
        $this->checkSign($param);
        return $next($request);
    }


    private function checkTime(){
        $time = time();
        if($this->timeStamp < $time - 600 || $this->timeStamp > $time + 600){
            throw new SignException('TIME_OUT');
        }
    }

    private function checkSign($params){
        ksort($params);
        $paramstr = '';
        foreach($params as $k=>$v){
            $paramstr .= "{$k}{$v}";
        }
        $secret = $this->secret;
        $timeStamp = $this->timeStamp;
        $makeSign = md5("{$secret}-{$paramstr}-{$timeStamp}");
        $result = [];
        if(env('APP_DEBUG')){ 
            $result = [
                'secret' => $secret,
                'timeStamp' => $timeStamp,
                'sign' => $makeSign,
                'request_sign' => $this->sign,
                'paramStr' => $paramstr
            ];
        }
        if($this->sign != $makeSign){
            throw new SignException('NO_PERMISSION',$result);
        }

    }

}
