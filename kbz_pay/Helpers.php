<?php
/**
 * Created by PhpStorm.
 * User: myathtut
 * Date: 5/6/18
 * Time: 9:31 PM
 */


if (! function_exists('fontDetect')) {
    function fontDetect($content, $default = "zawgyi")
    {
        if($content == null){
            $content = "";
        }
        return SteveNay\MyanFont\MyanFont::fontDetectByMachineLearning($content, $default);
    }
}

if (! function_exists('isMyanmarSar')) {
    function isMyanmarSar($content)
    {
        if($content == null){
            $content = "";
        }
        return SteveNay\MyanFont\MyanFont::isMyanmarSar($content);
    }
}

if (! function_exists('uni2zg')) {
    function uni2zg($content)
    {
        if($content == null){
            $content = "";
        }
        return SteveNay\MyanFont\MyanFont::uni2zg($content);
    }
}

if (! function_exists('zg2uni')) {
    function zg2uni($content)
    {
        if($content == null){
            $content = "";
        }
        return SteveNay\MyanFont\MyanFont::zg2uni($content);
    }
}

if (! function_exists('checkLoanApproveBtn')) {
    function checkLoanApproveBtn($loanRequest) : bool
    {
        return ($loanRequest->currentScreen() == "loan") && (! in_array($loanRequest->status,['reject','close']));
    }
}

if (! function_exists('getNthObjectFromCollection')) {
    function getApprovedPaidLog($is_paid_user_log, $schedule) {
        return $is_paid_user_log->filter(function ($log) use ($schedule) {
            $data = json_decode($log->detail);

            return $data->id == $schedule->id;
        })->first();
    }
}

if (! function_exists('getArrayFromUrlEncode')) {
    function getArrayFromUrlEncode($encodedStr) {
        $d = [];
        foreach (explode('&', urldecode($encodedStr)) as $key => $value) {
            $value = explode('=', $value);
            $d[$value[0]] = (int) $value[1];
        }
        return $d;
    }
}