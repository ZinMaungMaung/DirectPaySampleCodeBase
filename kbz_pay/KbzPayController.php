<?php
class KbzPayContoller{

    public function kbzpay(Request $request)
    {
        $data = $this->getPrecreateData($request);

        $kbzPay = Kbzpay::create([
            'loan_type' => $request->loan_type,
            'loan_request_id' => $request->loan_request_id,
            'payment_schedule_id' => $request->payment_schedule_id,
            'purchase_amount' => $request->amount,
        ]);

        LogInsert::logInsert("Response KBZ Pay Precreate Before", json_encode(["Request" => $data]),0, "kbz_pay_precreate", $request->loan_request_id);

        $client = new Client();
        $url = config('kbz_pay.precreate_url');

        $response = $client->request('POST', $url,  [
            'json' => ["Request" => $data],
        ]);

        $response = json_decode($response->getBody(), true);

        if ($response['Response']['result'] === 'SUCCESS') {
            LogInsert::logInsert("Response KBZ Pay Precreate Success", json_encode($response),0, "kbz_pay_precreate", $request->loan_request_id);

            $kbzPay->update([
                'prepay_id' => $response['Response']['prepay_id'],
                'merch_order_id' => $response['Response']['merch_order_id'],
            ]);

            return response()->json([
                'code' => Response::HTTP_OK,
                'message' => 'SUCCESS',
                'data' => $this->getPwaURL($response),
            ], 200);
        }

        LogInsert::logInsert("Response KBZ Pay Precreate Failed", json_encode($response),0, "kbz_pay_precreate", 0);

        return response()->json([
            'code' => 403,
            'message' => $response['Response']['msg'],
        ], 200);
    }

    protected function getPrecreateData(Request $request) : array
    {
        $data = $this->getWithoutSignData($request);
        $biz_content = collect($data['biz_content']);
        $dataArray = collect($data)->except(['biz_content', 'sign_type'])
                                ->merge($biz_content)->all();
        LogInsert::logInsert("Kbzpay sign Before", json_encode($dataArray),0, "nonce_str", $request->loan_request_id);
        $data['sign'] = $this->getSign($dataArray);
        LogInsert::logInsert("Kbzpay sign After", json_encode($data),0, "nonce_str", $request->loan_request_id);

        return $data;
    }

     protected function getWithoutSignData(Request $request) : array
    {
        $q = http_build_query($request->all());
        $encodedStr = urlencode($q);

        return [
            "timestamp" => (string) time(),
            "method" => "kbz.payment.precreate",
            "notify_url" => url('/api/repayments/kbz_pay/notify'),
            "nonce_str" => Str::random(32),
            "sign_type" => "SHA256",
            "version" => "1.0",
            "biz_content" => [
                "merch_order_id" => 'mother_' . $request->payment_schedule_id,
                "merch_code" => config('kbz_pay.merch_code'),
                "appid" => config('kbz_pay.appid'),
                "trade_type" => "PWAAPP",
                "total_amount" => $this->getTotalAmount($request->amount),
                "trans_currency" => "MMK",
                "timeout_express" => "100m",
                "callback_info" => $encodedStr,
            ],
        ];
    }

    public function getTotalAmount($amount)
    {
        return number_format((float) ($amount * 100)/98, 2, '.', '');
    }

    public function getSign($data)
    {
        ksort($data);

        $string = http_build_query($data) . "&key=" . config('kbz_pay.key');

        $sign = hash('sha256', $string);

        return strtoupper($sign);
    }

    public function getPwaURL($response)
    {
        $pwaBaseURL = config('kbz_pay.pwa_url');

        $responseData = [
            'appid' => config('kbz_pay.appid'),
            'merch_code' => config('kbz_pay.merch_code'),
            'nonce_str' =>  $response['Response']['nonce_str'],
            'prepay_id' => $response['Response']['prepay_id'],
            'timestamp' => time(),
        ];

        $pwaBaseURL .= http_build_query($responseData);

        $sign = $this->getSign($responseData);

        return 'sign=' . $sign;
    }

/**
*   notify url callback
*/
    public function kbzpayNotify(Request $request)
    {
        $requestData = $request->Request;

        $callback_info = getArrayFromUrlEncode($requestData['callback_info']);

        LogInsert::logInsert("KBZ Pay Callback Success", json_encode($requestData),0, "kbz_pay_success", $callback_info['loan_request_id']);

        $kbzpay = Kbzpay::where('loan_type', $callback_info['loan_type'])
                        ->where('loan_request_id', $callback_info['loan_request_id'])
                        ->where('payment_schedule_id', $callback_info['payment_schedule_id'])
                        ->first();

        if ($responseData['trade_status'] === 'PAY_SUCCESS' && $kbzpay) {

            $response = $this->callQueryOrderURL($requestData);

            LogInsert::logInsert("KBZ Pay Query Order", json_encode($response),0, "kbz_pay_query_order", $callback_info['loan_request_id']);

            if ($response['Response']['result'] === "SUCCESS" &&
                $response['Response']['trade_status'] === "PAY_SUCCESS") {

                $this->addRepayment($callback_info, $requestData);

                $kbzpay->update([
                    'notify_time' => $requestData['notify_time'],
                    'trade_status' => $requestData['trade_status'],
                    'trans_end_time' => $requestData['trans_end_time'],
                    'details' => json_encode($requestData),
                ]);

            }

            if ($response['Response']['result'] === "SUCCESS" &&
                $response['Response']['trade_status'] === "WAIT_PAY") {
                $this->sendSMSWhenPaymentFail($response);
            }
        }
    }

    protected function callQueryOrderURL($requestData)
    {
        $client = new Client();
        $url = config('kbz_pay.queryorder_url');

        $data = [
            "timestamp" => time(),
            "method" => "kbz.payment.queryorder",
            "nonce_str" => Str::random(32),
            "sign_type" => "SHA256",
            "version" => "1.0",
        ];

        $biz_content = [
            "appid" => config('kbz_pay.appid'),
            "merch_order_id" => $requestData['merch_order_id'],
            "merch_code" => config('kbz_pay.merch_code'),
            "mm_order_id" => $requestData['mm_order_id'],
        ];

        $sign = $this->getSign(collect($data)->merge(collect($biz_content))->except('sign_type')->all());

        $data['sign'] = $sign;
        $data['biz_content'] = $biz_content;

        $response = $client->request('POST', $url,  [
            'json' => ["Request" => $data],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function kbzWebView($prepay_id)
    {
        $link = $this->getPwaURL($prepay_id);
        $kbzPay = Kbzpay::where('prepay_id', $prepay_id)->first();
        $amount = $this->getTotalAmount($kbzPay->purchase_amount);

        return view('payment.kbz_pay', compact('link', 'amount'));
    }
}