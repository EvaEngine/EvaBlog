<?php
namespace Eva\EvaBlog\Forms;

use Eva\EvaEngine\Form;
use Phalcon\Forms\Element\Check;
use Eva\EvaBlog\Models;

class PostForm extends Form
{
    /**
     * @Type(Hidden)
     * @var integer
     */
    public $id;

    /**
     *
     * @Validator("PresenceOf", message = "Please input title")
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
     * @var string
     */
    public $flag;

    /**
     *
     * @var string
     */
    public $visibility;

    /**
     *
     * @var string
     */
    public $type;

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
    public $language;

    /**
     *
     * @var integer
     */
    public $parentId;

    /**
     *
     * @var string
     */
    public $slug;

    /**
     *
     * @var integer
     */
    public $sortOrder;

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
     * @var integer
     */
    public $editorId;

    /**
     *
     * @var string
     */
    public $editorName;

    /**
     * @Type(Select)
     * @Option(open=Open)
     * @Option(closed=Closed)
     * @var string
     */
    public $commentStatus;

    /**
     *
     * @var string
     */
    public $commentType;

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

    protected $categories;

    public function getCategories()
    {
        if ($this->categories) {
            return $this->categories;
        }
        $category = new Models\Category();
        $categories = $category->find(array(
            "order" => "id DESC",
            "limit" => 100
        ));

        $post = $this->getModel();
        $values = array();
        if ($post && $post->categories) {
            foreach ($post->categories as $categoryitem) {
                $values[] = $categoryitem->id;
            }
        }
        foreach ($categories as $key => $item) {
            $check = new Check('categories[]', array(
                'value' => $item->id
            ));
            if (in_array($item->id, $values)) {
                $check->setDefault($item->id);
            }
            $check->setLabel($item->categoryName);
            $this->categories[] = $check;
        }

        return $this->categories;
    }

    public function initialize($entity = null, $options = null)
    {
    }
}
