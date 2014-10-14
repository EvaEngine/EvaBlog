<?php
namespace Eva\EvaBlog\Forms;

use Eva\EvaEngine\Form;

/**
 * @package
 * @category
 * @subpackage
 *
 * @SWG\Model(id="PostStarForm")
 */
class StarForm extends Form
{
    /**
    * @var array<Stars>
     *
     * @SWG\Property(name="stars",type="array", items="$ref:StarPost")
     */
    public $stars;

    public function initialize($entity = null, $options = null)
    {
    }
}
