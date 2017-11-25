<?php
namespace MyOrg\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

/**
 * Class \MyOrg\Model\DogBreed
 *
 * @property string $Name
 * @method \SilverStripe\ORM\DataList|\MyOrg\Model\Dog[] Dogs()
 */
class DogBreed extends DataObject
{
    private static $db = [
        'Name' => 'Varchar(255)'
    ];

    private static $has_many = [
        'Dogs' => Dog::class
    ];

    /**
     * @param null|Member $member
     * @return bool
     */
    public function canView($member = null)
    {
        return true;
    }
}
