<?php
public function repayment_by_wave_pay(Request $request){
        if($request->all())
        {
            LogInsert::logInsert("Insert Wave Money Response Before", json_encode($request->all()),0, "wave_money_response", 0);

            if($request->statusCode == "107" && $request->statusDescription == "Transaction successfully completed")

            {
                $wavepay=new WavePay();
                $wavepay->phone_number= '95'.$request->purchaserMsisdn;
                $wavepay->purchase_amount=$request->purchaserAmount;
                $wavepay->waveMoneyReference=$request->waveMoneyReference;
                $hashValue=$request->hashValue;
                $hashdata=explode('-',$hashValue);
                $wavepay->loan_request_id=$hashdata[0];
                $wavepay->customer_id=$hashdata[1];
                $wavepay->loan_type=$hashdata[2];
                $wavepay->schedule_id=$hashdata[3];
                $wavepay->detail=json_encode($request->all());
                $wavepay->save();
                if($hashdata[2] == 'eloan')
                {
                    $repayment = new RePayment();
                    $repayment->amount = $request->purchaserAmount;
                    $repayment->actual_transaction_id = $request->waveMoneyReference;
                    $repayment->transaction_type_id = 7;
                    $repayment->type = 'scheduled';
                    $repayment->transaction_detail = \GuzzleHttp\json_encode($request->all());
                    $repayment->is_paid_at = Carbon::now();
                    $repayment->save();

                    $schedule = PaymentSchedule::findOrFail($hashdata[3]);
                    Log::info('schdule', ["schdule" => $schedule]);

                        info("Invoice No", ["Schdule" => $schedule]);
                        $schedule_repyament_connector = new ScheduleRepaymentConnector();
                        $schedule_repyament_connector->payment_schedule_id = $hashdata[3];
                        $schedule_repyament_connector->repayment_id = $repayment->id;
                        $schedule_repyament_connector->save();

                        Log::info("Reapment", ["Repayment" => $repayment->amount]);
                        Log::info("Schdule", ["Schdule" => $schedule->due_amount]);
                        $schedule->updatePaidStatus();

                        LogInsert::logInsert("Eloan repayment by wave pay", json_encode($request->all()),0, "wave_money_response", $hashdata[1]);
                    }


                else if($hashdata[2] == 'sloan')
                {
                    $repayment = new MicroLoanRepayment();
                    $repayment->transaction_type_id = 7;
                    $repayment->amount = $request->purchaserAmount;
                    $repayment->actual_transaction_id =$request->waveMoneyReference;
                    $repayment->type = 'scheduled';
                    $repayment->transaction_detail = \GuzzleHttp\json_encode($request->all());
//        $repayment->image = $url;
                    $repayment->is_paid_at = Carbon::now();
                    $repayment->save();

                    $schedule = MicroLoanPaymentSchdule::findOrFail($hashdata[3]);
                    if($schedule)
                    {
                        $schedule_repayment_connector = new MicroLoanPaymentConnector();
                        $schedule_repayment_connector->micro_loan_repayment_id = $repayment->id;
                        $schedule_repayment_connector->micro_loan_payment_schdule_id = $schedule->id;
                        $schedule_repayment_connector->save();
                    }
                    LogInsert::logInsert("Sloan repayment by wave pay", json_encode($request->all()),0, "wave_money_response", $hashdata[1]);
                 }
                else{
                    $repayment = new SmeRepayment();
                    $repayment->transaction_type_id = 7;
                    $repayment->amount = $request->purchaserAmount;
                    $repayment->actual_transaction_id = $request->waveMoneyReference;
                    $repayment->type = 'scheduled';
                    $repayment->transaction_detail = \GuzzleHttp\json_encode($request->all());
                    $repayment->is_paid_at = Carbon::now();
                    $repayment->save();

                    $schedule = SmePaymentSchedule::findOrFail($hashdata[3]);
                    if($schedule){
                        $schedule_repayment_connector = new SmeScheduleRepaymentConnector();
                        $schedule_repayment_connector->sme_payment_schedule_id = $schedule->id;
                        $schedule_repayment_connector->sme_repayment_id = $repayment->id;
                        $schedule_repayment_connector->save();
                    }

                    LogInsert::logInsert("SMEloan repayment by wave pay", json_encode($request->all()),0, "wave_money_response", $hashdata[1]);

                }


                LogInsert::logInsert("Insert Wave Money Response", json_encode($request->all()),0, "wave_money_response", $hashdata[1]);
                return $this->respondSuccessMsgOnly('success');
            }
            else{
                LogInsert::logInsert("Insert Wave Money Response", json_encode($request->all()),0, "wave_money_response", 0);
                return $this->respondSuccessMsgOnly('Response with '.$request->statusDescription);
            }


        }
        else{
            return $this->errorResponse('Data is empty');
        }

    }

