<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ConnectionsBundle\Sync\Extractor\Descriptor;

/**
 * Stores all registered Extraction descriptors.
 */
class ExtractionCollection
{
    /**
     * @var ExtractionDescriptorInterface[]
     */
    private $descriptors = [];

    /**
     * @param ExtractionDescriptorInterface $descriptor
     */
    public function addDescriptor(ExtractionDescriptorInterface $descriptor)
    {
        $this->descriptors[] = $descriptor;
    }

    /**
     * @return ExtractionDescriptorInterface[]
     */
    public function getDescriptors()
    {
        return $this->descriptors;
    }

    /**
     * @param ExtractionDescriptorInterface[] $descriptors
     */
    public function setDescriptors($descriptors)
    {
        $this->descriptors = $descriptors;
    }
}
