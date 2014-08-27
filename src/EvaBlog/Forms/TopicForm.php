<?php
namespace Eva\EvaBlog\Forms;

use Eva\EvaEngine\Form;
use Eva\EvaBlog\Models;

class TopicForm extends Form
{
    /**
     * @Type(Hidden)
     * @var integer
     */
    public $id;

    /**
     *
     * @Validator("PresenceOf", message = "Please input subject")
     * @var string
     */
    public $title;

    /**
     *
     * @Type(Select)
     * @Option(deleted=Deleted)
     * @Option(draft=Draft)
     * @Option(pending=Pending)
     * @Option(published=Published)
     * @var string
     */
    public $status;


    /**
     *
     * @Type(Hidden)
     * @var string
     */
    public $codeType;


    /**
     *
     * @var string
     */
    public $slug;


    /**
     *
     * @Type(Hidden)
     * @var integer
     */
    public $createdAt;

    /**
     *
     * @Type(Hidden)
     * @var integer
     */
    public $userId;

    /**
     *
     * @var string
     */
    public $username;

    /**
     *
     * @var integer
     */
    public $updatedAt;


    /**
     *
     * @var string
     */
    public $commentStatus;


    /**
     *
     * @var integer
     */
    public $commentCount;

    /**
     *
     * @var integer
     */
    public $count;

    /**
     *
     * @Type(Hidden)
     * @var integer
     */
    public $imageId;

    /**
     * @Type(Hidden)
     * @var string
     */
    public $image;

    /**
     *
     * @Type(TextArea)
     * @var string
     */
    public $summary;


    public function initialize($entity = null, $options = null)
    {
    }
}
