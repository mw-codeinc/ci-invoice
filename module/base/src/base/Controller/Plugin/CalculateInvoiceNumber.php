<?php

namespace base\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class CalculateInvoiceNumber extends AbstractPlugin
{
    public function __invoke($issueDate = null)
    {
        if(!is_null($issueDate)) {
            $issueDate = \DateTime::createFromFormat('d/m/Y', $issueDate);
            $query = $this->getController()->getEntityManager()->createQueryBuilder();
            $query->select('i')
                ->from('base\Entity\Invoice', 'i')
                ->where('i.dateIssue = ?1')
                ->andWhere('MONTH(i.dateIssue) = ?2')
                ->andWhere('i.account = ?3')
                ->orderBy('i.dateIssue', 'DESC')
                ->setMaxResults(1)
                ->setParameters(array(
                    '1' => $issueDate->format('Y-m-d'),
                    '2' => (int) $issueDate->format('m'),
                    '3' => null
                ));

            try {
                $rowInvoice = $query->getQuery()->getSingleResult();
            } catch (\Exception $ex) {
                $rowInvoice = null;
            }

            if(is_null($rowInvoice)) {
                $invoiceNumber = 1;
            } else {
                $invoiceNumber = (int)$rowInvoice->getNumber() + 1;
            }

            return $invoiceNumber;
        } else {
            return null;
        }
    }
}