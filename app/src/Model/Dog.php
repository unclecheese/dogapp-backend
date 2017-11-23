<?php
namespace MyOrg\Model;

use SilverStripe\GraphQL\Scaffolding\Interfaces\ScaffoldingProvider;
use SilverStripe\GraphQL\Scaffolding\Scaffolders\SchemaScaffolder;
use GraphQL\Type\Definition\ResolveInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Image;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class Dog extends DataObject implements ScaffoldingProvider
{
    private static $db = [
        'Name' => 'Varchar(255)',
    ];

    private static $has_one = [
        'Owner' => Member::class,
        'Breed' => DogBreed::class,
        'Image' => Image::class
    ];

    private static $many_many = [
        'FavouritingMembers' => [
            'through' => DogFavourite::class,
            'from' => 'Dog',
            'to' => 'Member',
        ],
    ];

    private static $has_many = [
        'Favourites' => DogFavourite::class,
    ];

    private static $default_sort = 'Created DESC';

    private static $casting = [
        'IsFavourite' => 'Boolean'
    ];

    public function getThumbnail()
    {
        return $this->Image()->exists() ? $this->Image()->Fill(300, 300)->AbsoluteURL : null;
    }

    public function canView($member = null)
    {
        return true;
    }

    public function getIsFavourite()
    {
        $memberID = Security::getCurrentUser()->ID;

        return (boolean)$this->FavouritingMembers()->byID($memberID);
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->Image()->exists()) {
            $this->Image()->copyVersionToStage('Stage', 'Live');
        }
    }

    public function provideGraphQLScaffolding(SchemaScaffolder $scaffolder)
    {
        $scaffolder
            ->mutation('toggleFavourite', __CLASS__)
            ->addArgs([
                'DogID' => 'ID!',
                'Favourite' => 'Boolean!',
            ])
            ->setResolver(function ($object, array $args, $context, ResolveInfo $info) {
                $dog = self::get()->byID($args['DogID']);
                if (!$dog) {
                    throw new \InvalidArgumentException(sprintf(
                        'Dog #%s does not exist',
                        $args['DogID']
                    ));
                }
                $params = [
                    'DogID' => $dog->ID,
                    'MemberID' => $context['currentUser']->ID
                ];

                $existing = DogFavourite::get()->filter($params)->first();

                if ((boolean)$existing === $args['Favourite']) {
                    return $dog;
                }

                if ($args['Favourite']) {
                    $favourite = DogFavourite::create($params);
                    $favourite->write();
                } else {
                    $existing->delete();
                }

                return $dog;

            });

        return $scaffolder;
    }
}
