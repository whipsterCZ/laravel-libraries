<?php

//UTILS
Route::get('migrate', [/*'middleware'=>'auth',*/
    function () {
        ob_start();
        $exitCode = Artisan::call('migrate');
        echo sprintf("<h1>artisan migrate: %s</h1>", $exitCode);
        echo "<pre>";
        echo \Artisan::output();
        echo "</pre>";
        return ob_get_clean();
    }
]);


Route::get('create-symlinks', [/*'middleware'=>'auth',*/
    function () {
        ob_start();
        echo sprintf("<h1>artisan symlinks</h1>");
        echo "<pre>";
        $exitCode = Artisan::call('symlinks:create');
        echo \Artisan::output();
        echo "</pre>";
        return ob_get_clean();
    }
]);



# Clear Cache command
Route::get('cache-clear',function(){
    Cache::flush();
    dd('cache cleared');
});