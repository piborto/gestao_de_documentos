<?php

if (!function_exists('password_verify')) {
    function password_verify($password, $hash) {
        if (empty($password) || empty($hash)) {
            return false;
        }
        
        $test_hash = crypt($password, $hash);
        
        return (strlen($test_hash) >= 13 && $test_hash === $hash);
    }
}