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
use base\Entity\Service;

class ServiceController extends AbstractActionController
{
    public function createAction()
    {
        $jsonModel = new JsonModel();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $dataArr = $this->params()->fromPost('data');
            $invoiceId = $this->params()->fromPost('invoiceKey');
            $invoice = $this->getEntityManager()->getRepository('base\Entity\Invoice')->findOneBy(array('id' => $invoiceId));
            try {
                if(!empty($dataArr) && !is_null($invoice)) {
                    foreach($dataArr as $data) {
                        $service = new Service();
                        $service->setInvoice($invoice);
                        $service->exchangeArray($data);
                        $this->getEntityManager()->persist($service);
                    }
                    $this->getEntityManager()->flush();

                    $jsonModel->setVariables(array(
                        'success' => true
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

    public function getServicesAction()
    {
        $jsonModel = new JsonModel();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $phrase = $this->params()->fromPost('phrase', null);

            try {
                if(!is_null($phrase)) {
                    $query = $this->getEntityManager()->createQueryBuilder();
                    $query->select('s.id as id', 's.name as name')
                        ->from('base\Entity\Service', 's')
                        ->where($query->expr()->orX(
                            $query->expr()->like('s.name', "'%".$phrase."%'")
                        ))
                        ->setMaxResults(10);

                    $rowset = $query->getQuery()->getResult();

                    $jsonModel = new JsonModel();
                    $jsonModel->setVariables(array(
                        'success' => true,
                        'rowset' => $rowset
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

