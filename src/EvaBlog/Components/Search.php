<?php

namespace Eva\EvaBlog\Components;

// +----------------------------------------------------------------------
// | [phalcon]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-8-26 14:13
// +----------------------------------------------------------------------

use Eva\EvaEngine\Mvc\User\Component;

class Search extends Component
{
    public function __invoke($params)
    {
        return $this->reDispatch(
            [
                'module' => 'EvaBlog',
                'namespace' => 'Eva\EvaBlog\Controllers',
                'controller' => 'post',
                'action' => 'search',
                'params' => $params,
            ]
        );
    }
}
