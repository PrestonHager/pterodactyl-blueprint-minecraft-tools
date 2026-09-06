<?php

namespace Blueprint\Facades;

use Illuminate\Support\Facades\Facade;

class ExtensionMenu extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'extension-menu';
    }
}
