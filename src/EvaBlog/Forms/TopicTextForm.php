<?php
namespace Eva\EvaBlog\Forms;

use Eva\EvaEngine\Form;

class TopicTextForm extends Form
{
    /**
     *
     * @var integer
     */
    public $topicId;

    /**
     *
     * @var string
     */
    public $metaKeywords;

    /**
     *
     * @Type(TextArea)
     * @var string
     */
    public $metaDescription;

    /**
     *
     * @var string
     */
    public $toc;

    /**
     * @Type(TextArea)
     * @var string
     */
    public $content;

    protected $defaultModelClass = 'Eva\EvaBlog\Entities\TopicTexts';
}
