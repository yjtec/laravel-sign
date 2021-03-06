<?php

namespace Yjtec\Sign;
use Closure;
use Illuminate\Support\Facades\Auth;
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
            $request->has('debug') ||
            !empty($request->header('x-ca-request-id'))
        ) {
            return $next($request);
        }

        if(!$app = Auth::guard('sign')->user()){
            throw new SignException('APP_ID_ERROR');
        }
        
        $param = $request->all();
        $appId = $request->appId;
        $timeStamp = $request->timeStamp;
        $sign      = $request->sign;
        if(!$appId || !$timeStamp || !$sign){
            throw new SignException('NO_PERMISSION');
        }
        $this->timeStamp = $timeStamp;
        $this->secret = $app->secret;
        $this->sign = $sign;
        unset($param['sign']);
        //清楚微信认证返回
        if(isset($param['state']) && $param['state'] == 'wx_oauth'){
            unset($param['code']);
            unset($param['state']);
        }
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
            if (is_array($v)){
                $paramstr .= "{$k}[]".implode(',',$v);
            } else {

                $paramstr .= "{$k}{$v}";
            }
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
