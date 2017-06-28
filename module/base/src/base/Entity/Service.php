<?php

namespace base\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="service")
 */
class Service {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

     /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="services")
     * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
     **/
    private $user;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     */
    protected $qty;

    /**
     * @ORM\Column(type="string")
     */
    protected $unit;

    /**
     * @ORM\Column(type="decimal", name="unitary_net_value", precision=8, scale=2)
     */
    protected $unitaryNetValue;

    /**
     * @ORM\Column(type="decimal", name="unitary_vat_value", precision=8, scale=2)
     */
    protected $unitaryVatValue;

    /**
     * @ORM\Column(type="decimal", name="net_value", precision=8, scale=2)
     */
    protected $netValue;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    protected $vat;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    protected $value;
    
    /**
     * @ORM\Column(type="datetime", name="date_create")
     */
    protected $dateCreate;

    /**
     * @ORM\Column(type="datetime", name="date_edit", nullable=true)
     */
    protected $dateEdit;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceService", mappedBy="service")
     */
    private $invoiceServices;

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
        $this->name             = strip_tags(trim($this->getName()));
        $this->qty              = strip_tags(trim($this->getQty()));
        $this->unit             = strip_tags(trim($this->getUnit()));
        $this->unitaryNetValue  = strip_tags(trim($this->getUnitaryNetValue()));
        $this->unitaryVatValue  = strip_tags(trim($this->getUnitaryVatValue()));
        $this->netValue         = strip_tags(trim($this->getNetValue()));
        $this->vat              = strip_tags(trim($this->getVat()));
        $this->value            = strip_tags(trim($this->getValue()));
    }

    public function __construct() {
        $this->invoiceServices = new ArrayCollection ();
    }

    public function getId() {
    	return $this->id;
    }
    
    public function getName() {
    	return $this->name;
    }

    public function getQty() {
        return $this->qty;
    }

    public function getUnit() {
        return $this->unit;
    }

    public function getUnitaryNetValue() {
        return $this->unitaryNetValue;
    }

    public function getUnitaryVatValue() {
        return $this->unitaryVatValue;
    }

    public function getNetValue() {
        return $this->netValue;
    }

    public function getVat() {
        return $this->vat;
    }

    public function getValue() {
        return $this->value;
    }
    
    public function getInvoiceRow() {
    	return $this->account;
    }

    public function setInvoice($account) {
        $this->account = $account;
        return $this;
    }

    public function exchangeArray($data) {
        $this->name             = (isset ( $data ['name'] )) ? $data ['name'] : null;
        $this->qty              = (isset ( $data ['qty'] )) ? $data ['qty'] : null;
        $this->unit             = (isset ( $data ['unit'] )) ? $data ['unit'] : null;
        $this->unitaryNetValue  = (isset ( $data ['unitaryNetValue'] )) ? $data ['unitaryNetValue'] : null;
        $this->unitaryVatValue  = (isset ( $data ['unitaryVatValue'] )) ? $data ['unitaryVatValue'] : null;
        $this->netValue         = (isset ( $data ['netValue'] )) ? $data ['netValue'] : null;
        $this->vat              = (isset ( $data ['vat'] )) ? $data ['vat'] : null;
        $this->value            = (isset ( $data ['value'] )) ? $data ['value'] : null;
    }
}