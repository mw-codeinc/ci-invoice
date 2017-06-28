<?php

namespace base\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="user", uniqueConstraints={@ORM\UniqueConstraint(name="email_uq", columns={"email"})})
 */
class User implements InputFilterAwareInterface {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $email;
	
	/**
	 * @ORM\Column(type="string", length=88)
	 */
	protected $password;
	
	/**
	 * @ORM\Column(type="string", name="first_name", length=150, nullable=true)
	 */
	protected $firstName;

    /**
     * @ORM\Column(type="string", name="last_name", length=150, nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $organization;
	
	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $address;
	
	/**
	 * @ORM\Column(type="string", length=50, nullable=true)
	 */
	protected $city;
	
	/**
	 * @ORM\Column(type="string",  name="zip_code", length=45, nullable=true)
	 */
	protected $zipCode;

    /**
     * @ORM\Column(type="string", length=45, nullable=true)
     */
    protected $country;
	
	/**
	 * @ORM\Column(type="boolean", name="is_active")
	 */
	protected $isActive;
	
	/**
	 * @ORM\Column(type="datetime", name="date_edit", nullable=true)
	 */
	protected $dateEdit;

    /**
     * @ORM\Column(type="datetime", name="date_create")
     */
    protected $dateCreate;

    /**
     * @ORM\OneToMany(targetEntity="Invoice", mappedBy="user")
     */
    private $invoices;

    /**
     * @ORM\OneToMany(targetEntity="Buyer", mappedBy="user")
     */
    private $buyers;

    /**
     * @ORM\OneToMany(targetEntity="Service", mappedBy="user")
     */
    private $services;
	
	protected $inputFilter;
	
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
	
	public function __construct() {
        $this->isActive = 1;
        $this->invoices = new ArrayCollection();
        $this->buyers   = new ArrayCollection();
        $this->services = new ArrayCollection();
    }
	
	public function getId() {
		return $this->id;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function getPassword() {
		return $this->password;
	}
	
	public function getFirstName() {
		return $this->firstName;
	}

    public function getLastName() {
        return $this->lastName;
    }

    public function getOrganization() {
        return $this->organization;
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
	
	public function isActive() {
		return $this->isActive;
	}

	public function setEmail($email) {
		$this->email = $email;
		return $this;
	}
	
	public function setIsActive($isActive) {
		$this->isActive = $isActive;
		return $this;
	}
	
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}
	
	public function exchangeArray($data) {
        $this->email            = (isset ( $data ['email'] )) ? $data ['email'] : null;
        $this->password         = (isset ( $data ['password'] )) ? $data ['password'] : null;
        $this->firstName        = (isset ( $data ['firstName'] )) ? $data ['firstName'] : null;
        $this->lastName         = (isset ( $data ['lastName'] )) ? $data ['lastName'] : null;
        $this->organization     = (isset ( $data ['organization'] )) ? $data ['organization'] : null;
        $this->address          = (isset ( $data ['address'] )) ? $data ['address'] : null;
        $this->city             = (isset ( $data ['city'] )) ? $data ['city'] : null;
        $this->zipCode          = (isset ( $data ['zipCode'] )) ? $data ['zipCode'] : null;
        $this->country          = (isset ( $data ['country'] )) ? $data ['country'] : null;
	}
	
	public function setInputFilter(InputFilterInterface $inputFilter) {}
	
	public function getInputFilter() {
		if (! $this->inputFilter) {
			$inputFilter = new InputFilter ();
			$factory = new InputFactory ();
			
			$inputFilter->add ( $factory->createInput ( array (
					'name' => 'email',
					'required' => true,
					'filters' => array (
							array (
									'name' => 'StripTags' 
							),
							array (
									'name' => 'StringTrim' 
							) 
					),
					'validators' => array (
							array (
									'name' => 'StringLength',
									'options' => array (
											'encoding' => 'UTF-8',
											'min' => 1,
											'max' => 250 
									) 
							),
							array (
									'name' => 'EmailAddress' 
							) 
					) 
			) ) );
			
			$this->inputFilter = $inputFilter;
		}
		
		return $this->inputFilter;
	}
}