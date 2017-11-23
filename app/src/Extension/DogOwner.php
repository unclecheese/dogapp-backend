<?php

namespace MyOrg\Extension;

use SilverStripe\ORM\DataExtension;
use MyOrg\Model\Dog;
use SilverStripe\Assets\Image;

class DogOwner extends DataExtension
{

    private static $has_many = [
        'Dogs' => Dog::class
    ];

    private static $has_one = [
        'ProfileImage' => Image::class,
    ];

    public function getThumbnail()
    {
        return $this->owner->ProfileImage()->exists() ?
            $this->owner->ProfileImage()->Fill(300, 300)->AbsoluteURL :
            null;
    }

    public function canView($member = null)
    {
        return true;
    }
}
