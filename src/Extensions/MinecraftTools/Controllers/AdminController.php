<?php

namespace App\Extensions\MinecraftTools\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function index()
    {
        return view('minecraft-tools::admin.view');
    }

    public function plugins()
    {
        return view('minecraft-tools::admin.plugins');
    }

    public function versions()
    {
        return view('minecraft-tools::admin.versions');
    }

    public function players()
    {
        return view('minecraft-tools::admin.players');
    }

    public function modpacks()
    {
        return view('minecraft-tools::admin.modpacks');
    }

    public function config()
    {
        return view('minecraft-tools::admin.config');
    }

    public function icon()
    {
        return view('minecraft-tools::admin.icon');
    }
}