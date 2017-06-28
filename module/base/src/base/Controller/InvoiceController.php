<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace base\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use base\Library\ExchangeRate;
use base\Entity\Invoice;

class InvoiceController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function summaryAction()
    {
        $this->layout('layout/404');
        $this->getResponse()->setStatusCode(404);
        return;
    }

    public function newAction()
    {
        try {
            $exchangeRates = new ExchangeRate('http://nbp.pl/kursy/xml/LastA.xml');
            $currency = $exchangeRates->find(array('kurs_sredni'));

            $usd = str_replace(",", ".", $currency['USD']['kurs_sredni']);
            $eur = str_replace(",", ".", $currency['EUR']['kurs_sredni']);

            return array(
                'usd'   => (float)$usd,
                'eur'   => (float)$eur
            );
        }
        catch(Exception $e) {
            return array(
                'usd'   => null,
                'eur'   => null
            );
        }
    }

    public function createAction()
    {
        $jsonModel = new JsonModel();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $dataArr = $this->params()->fromPost('data');
            try {
                if(!empty($dataArr)) {
                    $invoice = new Invoice($this->getEntityManager());
                    $invoice->setNumber($this->getInvoiceNumber((strip_tags(trim($dataArr['dateIssue'])))));
                    $invoice->exchangeArray($dataArr);
                    $this->getEntityManager()->persist($invoice);
                    $this->getEntityManager()->flush();

                    $jsonModel->setVariables(array(
                        'key'     => $invoice->getId(),
                        'success' => true
                    ));
                } else {
                    $jsonModel->setVariables(array(
                        'success' => false
                    ));
                }
            } catch (\Exception $ex) {
                var_dump($ex->getMessage());
                $jsonModel->setVariables(array(
                    'success' => false
                ));
            }
        }

        return $jsonModel;
    }

    public function pdfExportAction()
    {
        $now = new \DateTime();
        $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');

        $layoutViewModel = $this->layout();
        $layoutViewModel->setTemplate('layout/pdf');

        $viewModel = new ViewModel(array(
            'vars' => null,
        ));

        $viewModel->setTemplate('base/invoice/pdf-export');

        $layoutViewModel->setVariables(array(
            'content' => $viewRenderer->render($viewModel),
        ));

        $htmlOutput = $viewRenderer->render($layoutViewModel);

        $output = $this->serviceLocator->get('mvlabssnappy.pdf.service')->getOutputFromHtml($htmlOutput);

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'application/pdf');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"export-" . $now->format('d-m-Y H:i:s') . ".pdf\"");

        $response->setContent($output);

        return $response;
    }

    public function getPaymentDateAction()
    {
        $jsonModel = new JsonModel();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $issuedDate = $this->params()->fromPost('issuedDate');
            $daysToPayment = $this->params()->fromPost('daysToPayment');

            try {
                $issuedDate = str_replace("/", "-", $issuedDate);
                $date = new \DateTime($issuedDate);
                $date->add(new \DateInterval('P' . $daysToPayment . 'D'));
                $jsonModel->setVariables(array(
                    'success' => true,
                    'date' => $date->format('d/m/Y')
                ));
            } catch (\Exception $ex) {
                $jsonModel->setVariables(array(
                    'success' => false
                ));
            }
        }

        return $jsonModel;
    }

    public function convertAmountToWordsAction()
    {
        $jsonModel = new JsonModel();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $amount = $this->params()->fromPost('amount', null);
            $currency = $this->params()->fromPost('currency', null);

            try {
                if(!is_null($amount)) {
                    $jsonModel->setVariables(array(
                        'success' => true,
                        'convertedAmount' => $this->convertAmountToWordsPl((float) $amount, $currency)
                    ));
                } else {
                    $jsonModel->setVariables(array(
                        'success' => false
                    ));
                }
            } catch (\Exception $ex) {
                $jsonModel->setVariables(array(
                    'success' => false
                ));
            }
        }

        return $jsonModel;
    }
}

