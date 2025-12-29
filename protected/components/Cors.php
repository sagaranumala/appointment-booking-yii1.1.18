<?php
class Cors extends CFilter
{
    protected function preFilter($filterChain)
    {
      //  $allowedOrigin = 'http://localhost:3000'; // React frontend

        // header("Access-Control-Allow-Origin: $allowedOrigin");
        // header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        // header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token");
        // header("Access-Control-Allow-Credentials: true");

        // Stop OPTIONS preflight early
        // if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        //     header("HTTP/1.1 200 OK");
        //     exit(0);
        // }

        return true; // continue request
    }
}
