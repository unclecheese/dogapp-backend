<?php

use SilverStripe\Security\Member;

Member::get()->sort('ID DESC')->first()->login();