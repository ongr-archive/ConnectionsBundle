<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Tests\Functional\Fixtures\Bundles\Acme\TestBundle\Document;

use ONGR\ElasticsearchBundle\Annotation as ES;
use ONGR\ElasticsearchBundle\Document\DocumentInterface;

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
    private $title;

    /**
     * @var string
     *
     * @ES\Property(type="string", name="description")
     */
    private $description;

    /**
     * @var int
     *
     * @ES\Property(type="integer", name="price")
     */
    private $price;

    /**
     * @var string
     *
     * @ES\Property(type="geo_point", name="location")
     */
    private $location;

    /**
     * @var UrlObject[]|\Iterator
     *
     * @ES\Property(type="object", objectName="AcmeTestBundle:UrlObject", multiple=true, name="url")
     */
    private $links;

    /**
     * @var Category[]|\Iterator
     *
     * @ES\Property(type="object", objectName="AcmeTestBundle:Category", multiple=true, name="categories")
     */
    private $categories;

    /**
     * @return \Iterator|Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param \Iterator|Category[] $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return \Iterator|UrlObject[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param \Iterator|UrlObject[] $links
     */
    public function setLinks($links)
    {
        $this->links = $links;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }
}
