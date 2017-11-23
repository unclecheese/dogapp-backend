<?php

namespace MyOrg\Extension;

use MyOrg\Model\DogFavourite;
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

    private static $many_many = [
        'FavouriteDogs' => [
            'through' => DogFavourite::class,
            'from' => 'Member',
            'to' => 'Dog',
        ],
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
