<?php
class MPUResponseValidator
{

    private $secret_key = "HMABYD2ODSDJ01BT11TL2QLW4PWGIMHO";
    private $mputMerchantID = "204104001003623";

    public function validate(array $post_data)
    {
        return $this->receive_response($post_data) &&
            $this->check_merchant_id($post_data['merchantID']);
    }

    private function check_merchant_id(string $merchantID) : bool
    {
        return $this->mputMerchantID === $merchantID;
    }

    private function receive_response(array $post_data) : bool
    {
        // hash checking
        if (isset($post_data["hashValue"])) {
            return $this->is_hash_value_matched($post_data);
        }

        return false;
    }

    private function create_signature_string($input_fields_array)
    {
        unset($input_fields_array["hashValue"]);    // exclude hash value from signature string

        sort($input_fields_array, SORT_STRING);

        $signature_string = "";
        foreach ($input_fields_array as $key => $value) {
            $signature_string .= $value;
        }

        return $signature_string;
    }

    private function generate_hash_value($signature_string)
    {

        $hash_value = hash_hmac('sha1', $signature_string, $this->secret_key, false);

        return strtoupper($hash_value);
    }

    private function is_hash_value_matched($post_data)
    {
        $is_matched = false;
        $signature_string = $this->create_signature_string($post_data);
        $generated_hash_value = $this->generate_hash_value($signature_string);
        $server_hash_value = $post_data['hashValue'];

        if ($generated_hash_value == $server_hash_value) {
            $is_matched = true;
        }

        return $is_matched;
    }

}