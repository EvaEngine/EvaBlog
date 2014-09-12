<?php
namespace Eva\EvaBlog\Forms;

use Eva\EvaEngine\Form;

/**
 * @package
 * @category
 * @subpackage
 *
 * @SWG\Model(id="StarForm")
 */
class StarForm extends Form
{
    /**
    * @var array<Stars>
     *
     * @SWG\Property(name="stars",type="array", items="$ref:PostStar")
     */
    public $stars;

    public function initialize($entity = null, $options = null)
    {
    }
}
