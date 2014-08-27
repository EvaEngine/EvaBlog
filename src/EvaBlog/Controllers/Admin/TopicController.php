<?php

namespace Eva\EvaBlog\Controllers\Admin;

// +----------------------------------------------------------------------
// | [phalcon]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-8-20 14:15
// +----------------------------------------------------------------------

use Eva\EvaBlog\Forms\TopicTextForm;
use Eva\EvaBlog\Models\Topic;

class TopicController extends ControllerBase
{
    public function indexAction()
    {

    }

    public function createAction()
    {
        $form = new TopicTextForm();
        $topic = new Topic();
        $form->setModel($topic);
        $form->addForm('text', 'Eva\EvaBlog\Forms\TopicTextForm');
        $this->view->setVar('form', $form);
        $this->view->setVar('item', $topic);

        if (!$this->request->isPost()) {
            return false;
        }
        $data = $this->request->getPost();
        if (!$form->isFullValid($data)) {
            return $this->showInvalidMessages($form);
        }

        try {
            $form->save('createTopic');
        } catch (\Exception $e) {
            return $this->showException($e, $form->getModel()->getMessages());
        }
        $this->flashSession->success('SUCCESS_TOPIC_CREATED');

        return $this->redirectHandler('/admin/topic/edit/' . $form->getModel()->id);
    }

    public function checkslugAction()
    {
        $this->view->disable();
        $slug = $this->dispatcher->getParam('slug');
        $topicId = intval($this->dispatcher->getParam('topicId'));
        if (Topic::checkSlugExists($slug, $topicId)) {
            $this->response->setStatusCode('409', 'Slug has been taken');
        } else {
            $this->response->setStatusCode('200', 'ok');
        }
    }
}
