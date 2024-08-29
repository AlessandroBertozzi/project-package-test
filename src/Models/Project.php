<?php

namespace Bertozzi\Project\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{

    protected $guarded = ['id'];

    function test()
    {
        return 'ciao';
    }


}
