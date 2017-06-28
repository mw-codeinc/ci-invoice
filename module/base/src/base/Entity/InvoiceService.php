<?php

namespace base\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="invoice_service")
 */
class InvoiceService {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Invoice", inversedBy="invoiceServices")
     * @ORM\JoinColumn(name="id_invoice", referencedColumnName="id")
     **/
    private $invoice;

    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="invoiceServices")
     * @ORM\JoinColumn(name="id_service", referencedColumnName="id")
     **/
    private $service;
	
	/**
	 * @ORM\Column(type="datetime", name="date_edit", nullable=true)
	 */
	protected $dateEdit;

    /**
     * @ORM\Column(type="datetime", name="date_create")
     */
    protected $dateCreate;

	/**
	 * @ORM\PrePersist
	 */
	public function setDateCreate() {
		$this->dateCreate = new \DateTime();
	}
	
	/**
	 * @ORM\PreUpdate
	 */
	public function setDateEdit() {
		$this->dateEdit = new \DateTime();
	}
	
	public function getId() {
		return $this->id;
	}

    public function getInvoiceRow() {
        return $this->invoice;
    }

    public function getServiceRow() {
        return $this->service;
    }

    public function setInvoice($invoice) {
        $this->invoice = $invoice;
        return $this;
    }

    public function setService($service) {
        $this->service = $service;
        return $this;
    }
}