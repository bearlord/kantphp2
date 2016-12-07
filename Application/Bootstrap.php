<?php

use Kant\Route\Route;
//
Route::get("/a", function(){ 
    return "Welcome To Kant Framework V2.1";
});

Route::get("/b", function(){
   throw new \Kant\Exception\KantException("学霸"); 
});