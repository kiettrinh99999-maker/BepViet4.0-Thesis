<?php

namespace App\Http\Controllers\API\V1\Region;
use App\Models\Region;
use App\Http\Controllers\API\V1\BaseCRUDController;
use Illuminate\Http\Request;

class RegionController extends BaseCRUDController
{
    protected function setModel(){
        $this->model = Region::class;
    }

    protected function rules($id = null)
    {
        return [
        ];
    }
}
