<?php
namespace Eva\EvaBlog\Forms;

use Eva\EvaEngine\Form;
use Phalcon\Forms\Element\Select;
use Eva\EvaBlog\Models;

class CategoryForm extends Form
{
    /**
     * @Type(Hidden)
     * @var integer
     */
    public $id;

    /**
     * @Validator("PresenceOf", message = "Please input category name")
     * @var string
     */
    public $categoryName;

    /**
     *
     * @var string
     */
    public $slug;

    /**
     * @Type(TextArea)
     * @var string
     */
    public $description;

    /**
     *
     * @var integer
     */
    public $parentId;

    /**
     *
     * @var integer
     */
    public $rootId;

    /**
     *
     * @var integer
     */
    public $sortOrder;

    /**
     *
     * @var integer
     */
    public $createdAt;

    /**
     *
     * @var integer
     */
    public $count;

    /**
     *
     * @var integer
     */
    public $leftId;

    /**
     *
     * @var integer
     */
    public $rightId;

    /**
     *
     * @Type(Hidden)
     * @var integer
     */
    public $imageId;

    /**
     *
     * @Type(Hidden)
     * @var string
     */
    public $image;

    public function initialize($entity = null, $options = null)
    {
        $select = new Select('parentId');
        $category = new Models\Category();

        $categories = $category->find(array(
            "order" => "id DESC",
            "limit" => 100
        ));
        $categoryArray = array('None');
        foreach ($categories as $key => $item) {
            $categoryArray[$item->id] = $item->categoryName;
        }
        $select->setOptions($categoryArray);
        $this->add($select);
    }
}
