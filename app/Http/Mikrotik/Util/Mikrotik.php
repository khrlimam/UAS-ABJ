<?php


namespace App\Http\Mikrotik\Util;


use Illuminate\Support\Facades\Auth;
use KhairulImam\ROSWrapper\Wrapper;
use League\Flysystem\Filesystem;

class Mikrotik
{

    public static function API(): Wrapper
    {
        return Auth::user()->mikrotik();
    }

    public static function File(): Filesystem
    {
        return Auth::user()->mikrotikFileSystem();
    }

}