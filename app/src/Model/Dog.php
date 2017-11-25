<?php
namespace MyOrg\Model;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\GraphQL\Scaffolding\Interfaces\ScaffoldingProvider;
use SilverStripe\GraphQL\Scaffolding\Scaffolders\SchemaScaffolder;
use GraphQL\Type\Definition\ResolveInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Class \MyOrg\Model\Dog
 *
 * @property string $Name
 * @property int $OwnerID
 * @property int $BreedID
 * @property int $ImageID
 * @method \SilverStripe\Security\Member Owner()
 * @method \MyOrg\Model\DogBreed Breed()
 * @method \SilverStripe\Assets\Image Image()
 * @method \SilverStripe\ORM\DataList|\MyOrg\Model\DogFavourite[] Favourites()
 * @method \SilverStripe\ORM\ManyManyList|\SilverStripe\Security\Member[] FavouritingMembers()
 */
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

    /**
     * Get a thumbnail of the dog
     *
     * @return null|AssetContainer
     */
    public function getThumbnail()
    {
        return $this->Image()->exists() ? $this->Image()->Fill(300, 300)->AbsoluteURL : null;
    }

    /**
     * @param null|Member $member
     * @return bool
     */
    public function canView($member = null)
    {
        return true;
    }

    /**
     * Is this dog a favourite of mine?
     *
     * @return bool
     */
    public function getIsFavourite()
    {
        $memberID = 0;
        $member = Security::getCurrentUser();
        if ($member) {
            $memberID = $member->ID;
        }

        return (boolean)$this->FavouritingMembers()->byID($memberID);
    }

    /**
     * Publish the dog's image after write
     */
    public function onAfterWrite()
    {
        parent::onAfterWrite();

        if ($this->Image()->exists()) {
            $this->Image()->copyVersionToStage('Stage', 'Live');
        }
    }

    /**
     * Scaffold favouriting dogs for GraphQL
     *
     * We throw two separate InvalidArgumentExceptions. A tad odd, but sure.
     *
     * @param SchemaScaffolder $scaffolder
     * @return SchemaScaffolder
     * @throws ValidationException
     * @throws \InvalidArgumentException
     * @throws InvalidArgumentException
     */
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
