<?php
/**
 * Created by PhpStorm.
 * User: haohong
 * Date: 14-11-24
 * Time: 上午11:10
 */
/**
 * Created by PhpStorm.
 * User: wscn
 * Date: 14-11-24
 * Time: 上午11:10
 */

namespace Eva\EvaBlog\Controllers\Admin;

use Eva\EvaBlog\Models;
use Eva\EvaEngine\Mvc\Controller\JsonControllerInterface;
use Eva\EvaEngine\Exception;


class CategoryProcessController extends ControllerBase implements JsonControllerInterface {

    /**
     * @operationName("Change category sort order")
     * @operationDescription("Change category sort order")
     */
    public function sortAction()
    {
        if (!$this->request->isPut()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $id = $this->dispatcher->getParam('id');
        $category =  Models\Category::findFirst($id);
        try {
            $category->sortOrder = (int) $this->request->getPut('sortOrder');
            $category->save();
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $category->getMessages());
        }

        return $this->response->setJsonContent($category);
    }
} 