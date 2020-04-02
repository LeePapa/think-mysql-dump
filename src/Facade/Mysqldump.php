<?php

namespace xiaodi\Mysqldump\Facade;

use think\Facade;

class Mysqldump extends Facade
{
    /**
     * 获取当前Facade对应类名
     * @access protected
     * @return string
     */
    protected static function getFacadeClass()
    {
        return \xiaodi\Mysqldump\Mysqldump::class;
    }
}
