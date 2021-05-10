<?php

namespace Hydrators;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Hydrator\HydratorException;
use Doctrine\ODM\MongoDB\Hydrator\HydratorInterface;
use Doctrine\ODM\MongoDB\Query\Query;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ODM. DO NOT EDIT THIS FILE.
 */
class DocumentsOrderHydrator implements HydratorInterface
{
    private $dm;
    private $unitOfWork;
    private $class;

    public function __construct(DocumentManager $dm, UnitOfWork $uow, ClassMetadata $class)
    {
        $this->dm = $dm;
        $this->unitOfWork = $uow;
        $this->class = $class;
    }

    public function hydrate(object $document, array $data, array $hints = array()): array
    {
        $hydratedData = array();

        /** @Field(type="id") */
        if (isset($data['_id']) || (! empty($this->class->fieldMappings['orderId']['nullable']) && array_key_exists('_id', $data))) {
            $value = $data['_id'];
            if ($value !== null) {
                $typeIdentifier = $this->class->fieldMappings['orderId']['type'];
                $return = $value instanceof \MongoDB\BSON\ObjectId ? (string) $value : $value;
            } else {
                $return = null;
            }
            $this->class->reflFields['orderId']->setValue($document, $return);
            $hydratedData['orderId'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['location']) || (! empty($this->class->fieldMappings['location']['nullable']) && array_key_exists('location', $data))) {
            $value = $data['location'];
            if ($value !== null) {
                $typeIdentifier = $this->class->fieldMappings['location']['type'];
                $return = (string) $value;
            } else {
                $return = null;
            }
            $this->class->reflFields['location']->setValue($document, $return);
            $hydratedData['location'] = $return;
        }

        /** @Field(type="int") */
        if (isset($data['locationId']) || (! empty($this->class->fieldMappings['locationId']['nullable']) && array_key_exists('locationId', $data))) {
            $value = $data['locationId'];
            if ($value !== null) {
                $typeIdentifier = $this->class->fieldMappings['locationId']['type'];
                $return = (int) $value;
            } else {
                $return = null;
            }
            $this->class->reflFields['locationId']->setValue($document, $return);
            $hydratedData['locationId'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['server']) || (! empty($this->class->fieldMappings['server']['nullable']) && array_key_exists('server', $data))) {
            $value = $data['server'];
            if ($value !== null) {
                $typeIdentifier = $this->class->fieldMappings['server']['type'];
                $return = (string) $value;
            } else {
                $return = null;
            }
            $this->class->reflFields['server']->setValue($document, $return);
            $hydratedData['server'] = $return;
        }

        /** @Field(type="string") */
        if (isset($data['customer']) || (! empty($this->class->fieldMappings['customer']['nullable']) && array_key_exists('customer', $data))) {
            $value = $data['customer'];
            if ($value !== null) {
                $typeIdentifier = $this->class->fieldMappings['customer']['type'];
                $return = (string) $value;
            } else {
                $return = null;
            }
            $this->class->reflFields['customer']->setValue($document, $return);
            $hydratedData['customer'] = $return;
        }

        /** @Many */
        $mongoData = isset($data['items']) ? $data['items'] : null;

        if ($mongoData !== null && ! is_array($mongoData)) {
            throw HydratorException::associationTypeMismatch('Documents\Order', 'items', 'array', gettype($mongoData));
        }

        $return = $this->dm->getConfiguration()->getPersistentCollectionFactory()->create($this->dm, $this->class->fieldMappings['items']);
        $return->setHints($hints);
        $return->setOwner($document, $this->class->fieldMappings['items']);
        $return->setInitialized(false);
        if ($mongoData) {
            $return->setMongoData($mongoData);
        }
        $this->class->reflFields['items']->setValue($document, $return);
        $hydratedData['items'] = $return;

        /** @Field(type="int") */
        if (isset($data['discount']) || (! empty($this->class->fieldMappings['discount']['nullable']) && array_key_exists('discount', $data))) {
            $value = $data['discount'];
            if ($value !== null) {
                $typeIdentifier = $this->class->fieldMappings['discount']['type'];
                $return = (int) $value;
            } else {
                $return = null;
            }
            $this->class->reflFields['discount']->setValue($document, $return);
            $hydratedData['discount'] = $return;
        }

        /** @Field(type="int") */
        if (isset($data['total']) || (! empty($this->class->fieldMappings['total']['nullable']) && array_key_exists('total', $data))) {
            $value = $data['total'];
            if ($value !== null) {
                $typeIdentifier = $this->class->fieldMappings['total']['type'];
                $return = (int) $value;
            } else {
                $return = null;
            }
            $this->class->reflFields['total']->setValue($document, $return);
            $hydratedData['total'] = $return;
        }

        /** @Field(type="date") */
        if (isset($data['orderDate'])) {
            $value = $data['orderDate'];
            if ($value === null) { $return = null; } else { $return = \Doctrine\ODM\MongoDB\Types\DateType::getDateTime($value); }
            $this->class->reflFields['orderDate']->setValue($document, clone $return);
            $hydratedData['orderDate'] = $return;
        }
        return $hydratedData;
    }
}