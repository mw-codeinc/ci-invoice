<?php

namespace base\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="invoice")
 */
class Invoice {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="invoices")
	 * @ORM\JoinColumn(name="id_user", referencedColumnName="id")
	 */
	private $user;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $number;

    /**
     * @ORM\Column(type="string", name="full_number")
     */
    protected $fullNumber;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    protected $vat;

    /**
     * @ORM\Column(type="decimal", name="net_value", precision=8, scale=2)
     */
    protected $netValue;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2)
     */
    protected $value;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comments;

    /**
     * @ORM\Column(type="string", name="recipient_name", length=150, nullable=true)
     */
    protected $recipientName;

    /**
     * @ORM\Column(type="datetime", name="date_issue", nullable=true)
     */
    protected $dateIssue;

    /**
     * @ORM\Column(type="datetime", name="date_sell", nullable=true)
     */
    protected $dateSell;

    /**
     * @ORM\Column(type="datetime", name="date_payment", nullable=true)
     */
    protected $datePayment;

    /**
     * @ORM\Column(type="datetime", name="date_create")
     */
    protected $dateCreate;
	
	/**
	 * @ORM\Column(type="datetime", name="date_edit", nullable=true)
	 */
	protected $dateEdit;

	/**
	 * @ORM\OneToMany(targetEntity="InvoiceService", mappedBy="invoice")
	 */
	private $invoiceServices;

    /**
     * @ORM\OneToMany(targetEntity="InvoiceBuyer", mappedBy="invoice")
     */
    private $invoiceBuyers;
	
	/**
	 * @ORM\PrePersist
	 */
	public function setCreateAtDate() {
		$this->dateCreate = new \DateTime ();
	}

    /**
     * @ORM\PrePersist
     */
    private function prepareData() {
        $this->fullNumber       = (strip_tags(trim($this->getFullNumber())));
        $this->vat              = (strip_tags(trim($this->getVat())));
        $this->netValue         = (strip_tags(trim($this->getNetValue())));
        $this->value            = (strip_tags(trim($this->getValue())));
        $this->comments         = (strip_tags(trim($this->getComments())));
        $this->recipientName    = (strip_tags(trim($this->getRecipientName())));
        $this->dateIssue        = (strip_tags(trim($this->getDateIssue())));
        $this->dateSell         = (strip_tags(trim($this->getDateSell())));
        $this->datePayment      = (strip_tags(trim($this->getDatePayment())));
    }

    /**
     * @ORM\PreUpdate
     */
    public function setDateEdit() {
        $this->dateEdit = new \DateTime();
    }

	public function __construct() {
		$this->services         = new ArrayCollection ();
        $this->invoiceBuyers   = new ArrayCollection ();
	}
	
	public function getId() {
		return $this->id;
	}

    public function getNumber() {
        return $this->number;
    }

    public function getFullNumber() {
        return $this->fullNumber;
    }

    public function getVat() {
        return $this->vat;
    }

    public function getNetValue() {
        return $this->netValue;
    }

    public function getValue() {
        return $this->value;
    }

    public function getComments() {
        return $this->comments;
    }

    public function getRecipientName() {
        return $this->recipientName;
    }

    public function getDateIssue() {
        return $this->dateIssue;
    }

    public function getDateSell() {
        return $this->dateSell;
    }

    public function getDatePayment() {
        return $this->datePayment;
    }

    public function getAccountRow() {
        return $this->account;
    }
	
	public function setAccount($account) {
		$this->account = $account;
		return $this;
	}

    public function setNumber($number) {
        $this->number = $number;
        return $this;
    }

    public function exchangeArray($data) {
        $dateIssue = \DateTime::createFromFormat('d/m/Y', $data ['dateIssue']);
        $dateSell = \DateTime::createFromFormat('d/m/Y', $data ['dateSell']);
        $datePayment = \DateTime::createFromFormat('d/m/Y', $data ['datePayment']);

        $this->fullNumber       = (isset ( $data ['fullNumber'] )) ? $data ['fullNumber'] : null;
        $this->vat              = (isset ( $data ['vat'] )) ? $data ['vat'] : null;
        $this->netValue         = (isset ( $data ['netValue'] )) ? $data ['netValue'] : null;
        $this->value            = (isset ( $data ['value'] )) ? $data ['value'] : null;
        $this->comments         = (isset ( $data ['comments'] )) ? $data ['comments'] : null;
        $this->recipientName    = (isset ( $data ['recipientName'] )) ? $data ['recipientName'] : null;
        $this->dateIssue        = (is_a ( $dateIssue, 'DateTime' )) ? $dateIssue : null;
        $this->dateSell         = (is_a ( $dateSell, 'DateTime' )) ? $dateSell : null;
        $this->datePayment      = (is_a ( $datePayment, 'DateTime' )) ? $datePayment : null;
    }
}