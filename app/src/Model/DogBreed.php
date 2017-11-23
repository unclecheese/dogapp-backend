<?php
namespace MyOrg\Model;

use SilverStripe\ORM\DataObject;

class DogBreed extends DataObject
{
    private static $db = [
        'Name' => 'Varchar(255)'
    ];

    private static $has_many = [
        'Dogs' => Dog::class
    ];

    public function canView($member = null)
    {
        return true;
    }

}
