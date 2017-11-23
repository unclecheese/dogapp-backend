<?php

namespace MyOrg\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class DogFavourite extends DataObject
{
    private static $has_one = [
        'Member' => Member::class,
        'Dog' => Dog::class,
    ];

    private static $casting = [
        'IsMine' => 'Boolean',
    ];

    public function getIsMine()
    {
        return $this->MemberID === Security::getCurrentUser()->ID;
    }

    public function canCreate($member = null, $context = [])
    {
        return !!$member;
    }

    public function canEdit($member = null, $context = [])
    {
        return false;
    }

    public function canView($member = null, $context = [])
    {
        return true;
    }

    public function canDelete($member = null, $context = [])
    {
        return $member && $member->ID === $this->MemberID;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->MemberID = Security::getCurrentUser()->ID;
    }
}