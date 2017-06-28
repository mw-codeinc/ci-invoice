<?php

namespace base\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="buyer")
 */
class Buyer {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="buyers")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     **/
    private $user;
	
	/**
	 * @ORM\Column(type="string", length=250, nullable=true)
	 */
	protected $name;

    /**
     * @ORM\Column(type="string", name="vat_id", length=250)
     */
    protected $vatId;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $address;
	
	/**
	 * @ORM\Column(type="string", length=50)
	 */
	protected $city;
	
	/**
	 * @ORM\Column(type="string",  name="zip_code", length=45)
	 */
	protected $zipCode;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $country;
	
	/**
	 * @ORM\Column(type="datetime", name="date_edit", nullable=true)
	 */
	protected $dateEdit;

    /**
     * @ORM\Column(type="datetime", name="date_create")
     */
    protected $dateCreate;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceBuyer", mappedBy="buyer")
     */
    private $invoiceBuyers;

	/**
	 * @ORM\PrePersist
	 */
	public function setDateCreate() {
		$this->dateCreate = new \DateTime();
	}

    /**
     * @ORM\PrePersist
     */
    private function prepareData() {
        $this->name     = (strip_tags(trim($this->getName())));
        $this->vatId    = (strip_tags(trim($this->getVatId())));
        $this->address  = (strip_tags(trim($this->getAddress())));
        $this->city     = (strip_tags(trim($this->getCity())));
        $this->zipCode  = (strip_tags(trim($this->getZipCode())));
        $this->country  = (strip_tags(trim($this->getCountry())));
    }
	
	/**
	 * @ORM\PreUpdate
	 */
	public function setDateEdit() {
		$this->dateEdit = new \DateTime();
	}

    public function __construct() {
        $this->invoiceBuyers = new ArrayCollection ();
    }
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}

    public function getVatId() {
        return $this->vatId;
    }

	public function getAddress() {
		return $this->address;
	}
	
	public function getCity() {
		return $this->city;
	}
	
	public function getZipCode() {
		return $this->zipCode;
	}

    public function getCountry() {
        return $this->country;
    }

    public function getInvoiceRow() {
        return $this->account;
    }

    public function setInvoice($account) {
        $this->account = $account;
        return $this;
    }

	public function exchangeArray($data) {
        $this->name     = (isset ( $data ['name'] )) ? $data ['name'] : null;
        $this->vatId    = (isset ( $data ['vatId'] )) ? $data ['vatId'] : null;
        $this->address  = (isset ( $data ['address'] )) ? $data ['address'] : null;
        $this->city     = (isset ( $data ['city'] )) ? $data ['city'] : null;
        $this->zipCode  = (isset ( $data ['zipCode'] )) ? $data ['zipCode'] : null;
        $this->country  = (isset ( $data ['country'] )) ? $data ['country'] : null;
	}
}