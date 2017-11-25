<?php

namespace MyOrg\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Class \MyOrg\Model\DogFavourite
 *
 * @property int $MemberID
 * @property int $DogID
 * @method \SilverStripe\Security\Member Member()
 * @method \MyOrg\Model\Dog Dog()
 */
class DogFavourite extends DataObject
{
    private static $has_one = [
        'Member' => Member::class,
        'Dog' => Dog::class,
    ];

    private static $casting = [
        'IsMine' => 'Boolean',
    ];

    /**
     * See if the current Dog is my dog
     *
     * @return bool
     */
    public function getIsMine()
    {
        if (Security::getCurrentUser()) {
            return $this->MemberID === Security::getCurrentUser()->ID;
        }

        return false;
    }

    /**
     * @param null|Member $member
     * @param array $context
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        return (bool)$member;
    }

    /**
     * @param null|Member $member
     * @param array $context
     * @return bool
     */
    public function canEdit($member = null, $context = [])
    {
        return false;
    }

    /**
     * @param null|Member $member
     * @param array $context
     * @return bool
     */
    public function canView($member = null, $context = [])
    {
        return true;
    }

    /**
     * @param null|Member $member
     * @param array $context
     * @return bool
     */
    public function canDelete($member = null, $context = [])
    {
        return $member && $member->ID === $this->MemberID;
    }

    /**
     * Set the MemberID for the favourite
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->MemberID = Security::getCurrentUser()->ID;
    }
}
