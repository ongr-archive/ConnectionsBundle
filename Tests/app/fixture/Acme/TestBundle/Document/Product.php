<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\app\fixture\Acme\TestBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use ONGR\ElasticsearchBundle\Document\DocumentTrait;

/**
 * Product document for testing.
 *
 * @ES\Document(type="product")
 * @ES\Skip({"name"})
 * @ES\Inherit({"price"})
 */
class Product extends Item implements DocumentInterface
{
    /**
     * @var string
     *
     * @ES\Property(type="string", name="title", fields={@ES\MultiField(name="raw", type="string")})
     */
    public $title;

    /**
     * @var string
     *
     * @ES\Property(type="string", name="description")
     */
    public $description;

    /**
     * @var int
     *
     * @ES\Property(type="integer", name="price")
     */
    public $price;

    /**
     * @var string
     *
     * @ES\Property(type="geo_point", name="location")
     */
    public $location;

    /**
     * @var UrlObject[]|\Iterator
     *
     * @ES\Property(type="object", objectName="AcmeTestBundle:UrlObject", multiple=true, name="url")
     */
    public $links;

    /**
     * @var ImagesNested[]|\Iterator
     *
     * @ES\Property(type="nested", objectName="AcmeTestBundle:ImagesNested", multiple=true, name="images")
     */
    public $images;

    /**
     * @var Category[]|\Iterator
     *
     * @ES\Property(type="object", objectName="AcmeTestBundle:Category", multiple=true, name="categories")
     */
    public $categories;
}
