<?php
namespace Eva\EvaBlog\Forms;

use Eva\EvaEngine\Form;
use Phalcon\Forms\Element\Select;
use Eva\EvaBlog\Models;

class FilterForm extends Form
{
    /**
    * @Type(Hidden)
    * @var integer
    */
    public $uid;

    /**
    *
    * @var string
    */
    public $q;

    /**
    *
    * @Type(Select)
    * @Option("25":"25")
    * @Option("10":"10")
    * @Option("50":"50")
    * @Option("100":"100")
    * @var string
    */
    public $per_page;

    /**
    *
    * @Type(Select)
    * @Option("All Status")
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
    public $username;

    /**
    *
    * @var string
    */
    public $source_name;

    protected $cid;

    public function addCid()
    {
        if ($this->cid) {
            return $this->cid;
        }

        $category = new Models\Category();
        $categories = $category->find(array(
            "order" => "id DESC",
            "limit" => 100
        ));

        if ($categories) {
            $options = array('All Categories');
            foreach ($categories as $categoryitem) {
                $options[$categoryitem->id] = $categoryitem->categoryName;
            }
            $element = new Select('cid', $options);
        }
        $this->add($element);

        return $this->cid = $element;
    }

    public function initialize($entity = null, $options = null)
    {
        $this->initializeFormAnnotations();
        $this->addCid();
    }
}
