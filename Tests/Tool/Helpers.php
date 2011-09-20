<?php
if ( ! function_exists('site_url')) {
    function site_url($segments) {
        return 'http://localhost/' . implode('/', $segments);
    }
}

if ( ! function_exists('log_message')) {
    function log_message() {
        print 'log_message(): ' . implode(' ', func_get_args()) . "\n";
    }
}