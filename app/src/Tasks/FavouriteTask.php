<?php

namespace MyOrg\Tasks;

use MyOrg\Model\Dog;
use MyOrg\Model\DogBreed;
use MyOrg\Model\DogFavourite;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\File;
use SilverStripe\Dev\BuildTask;
use Faker\Factory;
use SilverStripe\Security\Member;
use SilverStripe\Assets\Image;

class FavouriteTask extends BuildTask
{
    private static $segment = 'populate-favourites';

    public function run($request)
    {
        // Ensure this task is idempotent
        DogFavourite::get()->removeAll();
        $dogIDs = Dog::get()->column('ID');
        $memberIDs = Member::get()->column('ID');

        for ($i = 0; $i < 50; $i++) {
            $fave = DogFavourite::create([
                'MemberID' => $this->randArr($memberIDs),
                'DogID' => $this->randArr($dogIDs),
            ]);
            $fave->write();

            echo "Created favourite {$fave->Member()->getName()} to {$fave->Dog()->Name}\n";
        }
        echo "Done\n";
    }

    private function randArr($arr, $count = 1)
    {
        shuffle($arr);

        if ($count > 0) {
            $arr = array_splice($arr, 0, $count);
        }

        return $count == 1 ? $arr[0] : $arr;
    }
}