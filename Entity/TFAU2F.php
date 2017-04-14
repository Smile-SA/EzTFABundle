<?php

namespace Smile\EzTFABundle\Entity;

/**
 * TFAU2F
 */
class TFAU2F
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $keyName;

    /**
     * @var string
     */
    private $keyHandle;

    /**
     * @var string
     */
    private $publicKey;

    /**
     * @var string
     */
    private $certificate;

    /**
     * @var int
     */
    private $counter;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return TFAU2F
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set keyName
     *
     * @param string $keyName
     *
     * @return TFAU2F
     */
    public function setKeyName($keyName)
    {
        $this->keyName = $keyName;

        return $this;
    }

    /**
     * Get keyName
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->keyName;
    }

    /**
     * Set keyHandle
     *
     * @param string $keyHandle
     *
     * @return TFAU2F
     */
    public function setKeyHandle($keyHandle)
    {
        $this->keyHandle = $keyHandle;

        return $this;
    }

    /**
     * Get keyHandle
     *
     * @return string
     */
    public function getKeyHandle()
    {
        return $this->keyHandle;
    }

    /**
     * Set publicKey
     *
     * @param string $publicKey
     *
     * @return TFAU2F
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * Get publicKey
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Set certificate
     *
     * @param string $certificate
     *
     * @return TFAU2F
     */
    public function setCertificate($certificate)
    {
        $this->certificate = $certificate;

        return $this;
    }

    /**
     * Get certificate
     *
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * Set counter
     *
     * @param integer $counter
     *
     * @return TFAU2F
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter
     *
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }
}
