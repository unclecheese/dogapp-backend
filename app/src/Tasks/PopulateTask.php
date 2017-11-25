<?php

namespace MyOrg\Tasks;

use MyOrg\Model\Dog;
use MyOrg\Model\DogBreed;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\File;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\BuildTask;
use Faker\Factory;
use SilverStripe\Security\Member;
use SilverStripe\Assets\Image;

class PopulateTask extends BuildTask
{
    private static $segment = 'populate-dogs';

    /**
     * Create some dogs to populate the DB
     *
     * @param HTTPRequest $request
     */
    public function run($request)
    {
        // Ensure this task is idempotent
        DogBreed::get()->removeAll();
        Dog::get()->removeAll();
        Member::get()->removeAll();
        File::get()->removeAll();
        $faker = Factory::create();

        $json = @file_get_contents(BASE_PATH . '/app/data/dogs.json');
        if ($json) {
            $list = json_decode($json, true);
            foreach ($list['dogs'] as $breed) {
                $record = DogBreed::create([
                    'Name' => $breed
                ]);
                $record->write();
            }
        }

        $dogFolder = Folder::find_or_make('dogs');
        if (!$dogFolder->myChildren()->count()) {
            for ($i = 0; $i < 20; $i ++) {
                $image = Image::create();
                $image->setFromStream(fopen('https://loremflickr.com/320/240/dog', 'r'), "dogs/dog-$i.jpg");
                $image->ParentID = $dogFolder->ID;
                $image->write();
                $image->copyVersionToStage('Stage', 'Live');
                echo "Downloaded image {$image->Filename}\n";
            }
        }

        $peopleFolder = Folder::find_or_make('members');
        if (!$peopleFolder->myChildren()->count()) {
            for ($i = 0; $i < 20; $i ++) {
                $image = Image::create();
                $image->setFromStream(fopen('https://loremflickr.com/320/240/face', 'r'), "members/member-$i.jpg");
                $image->ParentID = $peopleFolder->ID;
                $image->write();
                $image->copyVersionToStage('Stage', 'Live');
                echo "Downloaded image {$image->Filename}\n";
            }
        }
        $imageIDs = $peopleFolder->myChildren()->column('ID');

        for ($i = 0; $i < 100; $i ++) {
            $member = Member::create([
                'FirstName' => $faker->firstName,
                'Surname' => $faker->lastName,
                'ProfileImageID' => $this->randArr($imageIDs),
            ]);
            $member->write();
            echo "Created member {$member->getName()}\n";
        }
        $memberIDs = Member::get()->column('ID');
        $breedIDs = DogBreed::get()->column('ID');
        $imageIDs = $dogFolder->myChildren()->column('ID');

        for ($i = 0; $i < 100; $i ++) {
            $dog = Dog::create([
                'Name' => $faker->firstName,
                'OwnerID' => $this->randArr($memberIDs),
                'BreedID' => $this->randArr($breedIDs),
                'ImageID' => $this->randArr($imageIDs),
            ]);
            $dog->write();
            echo "Created dog {$dog->Name}\n";
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
