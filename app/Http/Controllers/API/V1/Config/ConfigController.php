<?php

namespace App\Http\Controllers\API\V1\Config;

use Illuminate\Http\Request;
use App\Models\SettingWeb;
use App\Http\Controllers\API\V1\BaseCRUDController;
class ConfigController extends BaseCRUDController
{
        protected function setModel(){
        $this->model=SettingWeb::class;
    }
    protected function rules($id = null)
    {
        return [
        ];
    }
}
