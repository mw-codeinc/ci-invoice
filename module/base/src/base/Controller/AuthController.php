<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace base\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use base\Entity\User;
use base\Form\LoginForm;
use base\Form\LoginFilter;
use Zend\Session\Container;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Sendmail;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class AuthController extends AbstractActionController
{
    public function loginAction()
    {
        $this->layout('layout/login');

        $this->getServiceLocator()->get('viewhelpermanager')->get('headLink')->appendStylesheet('/css/login-soft.css');
        $this->getServiceLocator()->get('viewhelpermanager')->get('inlineScript')->appendFile('/js/login-soft.js');

    	$authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
    	if ($authService->hasIdentity()) {
    		return $this->redirect()->toRoute('login');
    	}
    	
		$form = new LoginForm();
		$container = new Container('ci_invoice');
		$container->getManager()->getStorage()->clear('ci_invoice');

		$request = $this->getRequest();
		if ($request->isPost()) {
			$form->setInputFilter ( new LoginFilter ( $this->getServiceLocator () ) );
			$form->setData ($request->getPost());

			if ($form->isValid()) 	{
				$data = $form->getData();
				$authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
				$adapter = $authService->getAdapter();
				$adapter->setIdentityValue($data['email']);
				$adapter->setCredentialValue($data['password']);
				$authResult = $authService->authenticate();
				if ($authResult->isValid()) {
					$identity = $authResult->getIdentity();
					$authService->getStorage()->write($identity);
					return $this->redirect()->toRoute('teacher');
				} else {
					return new ViewModel ( array (
						'error' => 'Your credentials are not valid',
						'form' => $form
					) );
				}
			} else {
				return new ViewModel ( array (
					'error' => 'Your credentials are not valid',
					'form' => $form
				) );
			}
		}
		return new ViewModel ( array (
			'form' => $form
		) );
    }

    public function logoutAction()
    {
    	$authService = $this->getServiceLocator()->get('Zend\Authentication\AuthenticationService');
    	$authService->clearIdentity();
    	$sessionManager = new \Zend\Session\SessionManager();
    	$sessionManager->forgetMe();
    	
    	return $this->redirect()->toRoute('login');
    }

    public function passwordRecoveryAction()
    {
        $jsonModel = new JsonModel();
        $entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $uri = $this->getRequest()->getUri();
        $baseUri = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
        $token = null;
        $request = $this->getRequest();

        if($request->isXmlHttpRequest()) {
	        if ($request->isPost()) {
	            $accountEmail = $this->params()->fromPost('recovery-email');
	            $account = $entityManager->getRepository('base\Entity\Account')->findOneBy(array('email' => $accountEmail));
	
	            if(!is_null($account)) {
	            	/* mark all previous recovery objects owned by current user as inactive */
	            	$recoveryRows = $entityManager->getRepository('base\Entity\AccountAction')->findBy(array('account' => $account, 'type' => 'recovery', 'isActive' => 1));
	            	foreach($recoveryRows as $row) {
	            		$row->setIsActive(0);
	            		$entityManager->persist($row);
	            	}
	            	
	                $token = md5(microtime());
	                $passwordRecovery = new AccountAction();
	                $passwordRecovery->setAccount($account);
	                $passwordRecovery->setToken($token);
	                $passwordRecovery->setIsActive(1);
	                $passwordRecovery->setType('recovery');
	                $entityManager->persist($passwordRecovery);
	                $entityManager->flush();
	
	                $mailMessage = "
						We have received a request to reset the password to access your account. Please click on the following link to create a new password or copy and paste the entire link into your browser's address bar: <br><br>".
	                    $baseUri.$this->url()->fromRoute('password-reset', array('email' => $account->getEmail(), 'token' => $token))."<br><br>"."
						If you did not make this request, just delete this email. Nothing on your account has been changed. <br><br>
						If we can assist in any other way, please contact us at info@giamusicassessment.com"
	                ;
	
	                try {
	                    $html = new MimePart($mailMessage);
	                    $html->type = "text/html";
	                    $body = new MimeMessage();
	                    $body->setParts(array($html));
	                    $mail = new Mail\Message();
	                    $mail->setBody($body);
	                    $mail->setFrom('info@giamusicassessment.com', 'GiamusicAssessment Password Recovery');
	                    $mail->addTo($account->getEmail());
	                    $mail->setSubject('GiamusicAssessment Password Recovery');
	
	                    $transport = new Sendmail();
	                    $transport->send($mail);
	
	                    $jsonModel->setVariables(array(
	                        'success' => true
	                    ));
	                } catch (Exception $ex) {
	                    $jsonModel->setVariables(array(
	                        'success' => false
	                    ));
	                }
	
	            } else {
	                $jsonModel->setVariables(array(
	                    'success' => false
	                ));
	            }
	        } else {
	            $jsonModel->setVariables(array(
	                'success' => false
	            ));
	        }
        } else {
        	$this->getResponse()->setStatusCode(404);
        	return;
        }

        return $jsonModel;
    }

    public function passwordResetAction()
    {
    	$form = new PasswordResetForm();
    	$request = $this->getRequest();
    	
    	$email = $this->params()->fromRoute('email');
    	$token = $this->params()->fromRoute('token');
    	
    	$entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$account = $entityManager->getRepository('base\Entity\Account')->findOneBy(array('email' => $email));

    	if(!is_null($account)) {
    		$accountAction = $entityManager->getRepository('base\Entity\AccountAction')->findOneBy(array('account' => $account, 'type' => 'recovery', 'isActive' => 1, 'token' => $token));
    		
    		if(!is_null($accountAction)) {
    			if ($request->isPost()) {
    				$form->setInputFilter ( new PasswordResetFilter ( $this->getServiceLocator () ) );
    				$form->setData ($request->getPost());
    				
    				if($form->isValid()) {
    					$data = $form->getData();
    					$account->setPassword($this->encrypt($data['password']));
    					$accountAction->setIsActive(0);
    					$entityManager->persist($account);
    					$entityManager->persist($accountAction);
    					$entityManager->flush();
    					
    					return new ViewModel( array(
    						'success' => 'Your password has been changed successfully. Please login with your email and new password.',
    						'form' => $form
    					));
    				} else {
    					return new ViewModel( array(
    						'passwordError' => 'Selected password is incorrect.',
    						'form' => $form
    					));
    				}
    			}
    		} else {
    			return new ViewModel( array(
    				'error' => 'Email or token is not valid.',
    				'form' => $form
    			));
    		}
    	} else {
    		return new ViewModel( array(
    			'error' => 'Email or token is not valid.',
    			'form' => $form
    		));
    	}
    	
    	return new ViewModel ( array (
    		'form' => $form
    	) );
    }
    
    public function registrationAction()
    {
    	$this->layout('layout/login-layout');
    	$this->getServiceLocator()->get('viewhelpermanager')->get('headLink')->appendStylesheet('/css/teacher/account/registration.css');
    	 
    	$request = $this->getRequest();
    	$form = new RegistrationForm();
    
    	if ($request->isPost()) {
    		$form->setInputFilter ( new RegistrationFilter( $this->getServiceLocator () ) );
    		$form->setData ($request->getPost());
    
    		if($form->isValid()) {
    			$uri 			= $this->getRequest()->getUri();
    			$entityManager 	= $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    			$baseUri 		= sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    			$data 			= $form->getData();
    			 
    			$account = new Account();
    			$account->setEmail($data['email']);
    			$account->setPassword($this->encrypt($data['password']));
    			$account->setIsActive(0);
    			$account->setCreditsAmount(20);
    			 
    			$token = md5(microtime());
    			$accountActivation = new AccountAction();
    			$accountActivation->setAccount($account);
    			$accountActivation->setToken($token);
    			$accountActivation->setIsActive(1);
    			$accountActivation->setType('activation');
    			 
    			$entityManager->persist($accountActivation);
    			$entityManager->persist($account);
    			$entityManager->flush();
    			 
    			$mailMessage = "
					We have received a request to activate the account on GiaMusicAssessment.com. Please click on the following link to activate your account or copy and paste the entire link into your browser's address bar: <br><br>".
    					$baseUri.$this->url()->fromRoute('activation', array('email' => $account->getEmail(), 'token' => $token))."<br><br>"."
					If you did not make this request, just ignore this email. <br><br>
					If we can assist in any other way, please contact us at musictesting@giamusic.com";
    			 
    			$html = new MimePart($mailMessage);
    			$html->type = "text/html";
    			$body = new MimeMessage();
    			$body->setParts(array($html));
    			$mail = new Mail\Message();
    			$mail->setBody($body);
    			$mail->setFrom('musictesting@giamusic.com', 'GiamusicAssessment Account Registration');
    			$mail->addTo($account->getEmail());
    			$mail->setSubject('GiamusicAssessment Account Registration');
    
    			$transport = new Sendmail();
    			$transport->send($mail);
    
    			return new ViewModel(array(
    				'success' => true
    			));
    		}
    	}
    	 
    	return new ViewModel(array(
    		'form' => $form
    	));
    }
    
    public function activationAction() {
    	$this->layout('layout/login-layout');
    	$this->getServiceLocator()->get('viewhelpermanager')->get('headLink')->appendStylesheet('/css/teacher/account/registration.css');
    	 
    	$email = $this->params()->fromRoute('email');
    	$token = $this->params()->fromRoute('token');
    
    	$entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$account = $entityManager->getRepository('base\Entity\Account')->findOneBy(array('email' => $email, 'isActive' => 0));
    	 
    	if(!is_null($account)) {
    		$accountActivation = $entityManager->getRepository('base\Entity\AccountAction')->findOneBy(array('account' => $account, 'type' => 'activation', 'isActive' => 1, 'token' => $token));
    
    		if(!is_null($accountActivation)) {
    			$account->setIsActive(1);
    			$accountActivation->setIsActive(0);
    			 
    			$entityManager->persist($account);
    			$entityManager->persist($accountActivation);
    			$entityManager->flush();
    			 
    			return new ViewModel( array(
    				'success' 	=> true,
    				'msg' 		=> 'Your account is now active. Please use button below to login.',
    			));
    		} else {
    			return new ViewModel( array(
    				'success' 	=> false,
    				'msg' 		=> 'Sorry. We couldn\'t activate your account. Token is not active or has expired.',
    			));
    		}
    	} else {
    		return new ViewModel( array(
    			'success' 	=> false,
    			'msg' 		=> 'Sorry. There is no account to activate using current URL.',
    		));
    	}
    	 
    	return new ViewModel();
    }
    
    public function resendActivationAction() {
    	$jsonModel = new JsonModel();
    	$entityManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$uri = $this->getRequest()->getUri();
    	$baseUri = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    	$token = null;
    	$request = $this->getRequest();
    	 
    	if($request->isXmlHttpRequest()) {
    		if ($request->isPost()) {
    			$accountEmail = $this->params()->fromPost('resend-email');
    			$account = $entityManager->getRepository('base\Entity\Account')->findOneBy(array('email' => $accountEmail, 'isActive' => 0));
    			 
    			if(!is_null($account)) {
    				/* mark all previous recovery objects owned by current user as inactive */
    				$activationRows = $entityManager->getRepository('base\Entity\AccountAction')->findBy(array('account' => $account, 'type' => 'activation', 'isActive' => 1));
    				foreach($activationRows as $row) {
    					$row->setIsActive(0);
    					$entityManager->persist($row);
    				}
    				 
    				$token = md5(microtime());
    				$accountActivation = new AccountAction();
    				$accountActivation->setAccount($account);
    				$accountActivation->setToken($token);
    				$accountActivation->setIsActive(1);
    				$accountActivation->setType('activation');
    				$entityManager->persist($accountActivation);
    				$entityManager->flush();
    				 
    				$mailMessage = "
					We have received a request to activate the account on GiaMusicAssessment.com. Please click on the following link to activate your account or copy and paste the entire link into your browser's address bar: <br><br>".
    					$baseUri.$this->url()->fromRoute('activation', array('email' => $account->getEmail(), 'token' => $token))."<br><br>"."
					If you did not make this request, just ignore this email. <br><br>
					If we can assist in any other way, please contact us at musictesting@giamusic.com";
    
    				try {
    					$html = new MimePart($mailMessage);
    					$html->type = "text/html";
    					$body = new MimeMessage();
    					$body->setParts(array($html));
    					$mail = new Mail\Message();
    					$mail->setBody($body);
    					$mail->setFrom('musictesting@giamusic.com', 'GiamusicAssessment Account Registration');
    					$mail->addTo($account->getEmail());
    					$mail->setSubject('GiamusicAssessment Account Registration');
    					 
    					$transport = new Sendmail();
    					$transport->send($mail);
    					 
    					$jsonModel->setVariables(array(
    						'success' => true,
    						'msg' => 'We sent activation link to your email. Please follow the instructions in the email message.',
    					));
    				} catch (Exception $ex) {
    					$jsonModel->setVariables(array(
    						'success' => false,
    						'msg' => $ex->getMessage()
    					));
    				}
    			} else {
    				$jsonModel->setVariables(array(
    					'success' => false,
    					'msg' => 'Sorry. There is no account to activate for current email address.'
    				));
    			}
    		} else {
    			$jsonModel->setVariables(array(
    				'success' => false
    			));
    		}
    	} else {
    		$this->getResponse()->setStatusCode(404);
    		return;
    	}
    	 
    	return $jsonModel;
    }
}
