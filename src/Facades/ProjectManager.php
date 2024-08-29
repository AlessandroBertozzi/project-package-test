<?php

namespace Bertozzi\Project\Facades;

use Illuminate\Support\Facades\Facade;

class ProjectManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'project';
    }
}
