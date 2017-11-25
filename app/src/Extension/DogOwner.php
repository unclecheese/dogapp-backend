<?php

namespace MyOrg\Extension;

use MyOrg\Model\DogFavourite;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\ORM\DataExtension;
use MyOrg\Model\Dog;
use SilverStripe\Assets\Image;
use SilverStripe\Security\Member;

/**
 * Class \MyOrg\Extension\DogOwner
 *
 * @property \SilverStripe\Security\Member|\MyOrg\Extension\DogOwner $owner
 * @property int $ProfileImageID
 * @method \SilverStripe\Assets\Image ProfileImage()
 * @method \SilverStripe\ORM\DataList|\MyOrg\Model\Dog[] Dogs()
 * @method \SilverStripe\ORM\ManyManyList|\MyOrg\Model\Dog[] FavouriteDogs()
 */
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

    /**
     * Does the owner have a profile image, then return that image.
     *
     * @return null|AssetContainer
     */
    public function getThumbnail()
    {
        return $this->owner->ProfileImage()->exists() ?
            $this->owner->ProfileImage()->Fill(300, 300)->AbsoluteURL :
            null;
    }

    /**
     * @param null|Member $member
     * @return bool
     */
    public function canView($member = null)
    {
        return true;
    }
}
