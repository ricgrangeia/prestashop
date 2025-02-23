<?php

/**
 * 2007-2022 Ifthenpay Lda
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @copyright 2007-2022 Ifthenpay Lda
 * @author    Ifthenpay Lda <ifthenpay@ifthenpay.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace PrestaShop\Module\Ifthenpay\Admin\Payments;

if (!defined('_PS_VERSION_')) {
    exit;
}


use PrestaShop\Module\Ifthenpay\Base\Payments\MultibancoBase;
use PrestaShop\Module\Ifthenpay\Contracts\Admin\AdminOrderInterface;

class MultibancoAdminOrder extends MultibancoBase implements AdminOrderInterface
{

    public function setSmartyVariables($paymentInDatabase)
    {
        if ($paymentInDatabase) {
            $this->smartyDefaultData->setEntidade($this->paymentDataFromDb['entidade']);
            $this->smartyDefaultData->setReferencia($this->paymentDataFromDb['referencia']);
            $this->smartyDefaultData->setValidade(
                (isset($this->paymentDataFromDb['validade']) && $this->paymentDataFromDb['validade'] != '') ?
                    (new \DateTime($this->paymentDataFromDb['validade']))->format('d-m-Y') : ''
            );
        } else {
            $this->smartyDefaultData->setEntidade($this->paymentGatewayResultData->entidade);
            $this->smartyDefaultData->setReferencia($this->paymentGatewayResultData->referencia);
            $this->smartyDefaultData->setValidade(
                (isset($this->paymentDataFromDb['validade']) && $this->paymentDataFromDb['validade'] != '') ?
                    (new \DateTime($this->paymentGatewayResultData->validade))->format('d-m-Y') : ''
            );
        }
    }

    public function getAdminOrder()
    {
        $this->setPaymentModel('multibanco');
        $this->getFromDatabaseById();
        if (!empty($this->paymentDataFromDb)) {
            $this->setSmartyVariables(true);
        } else {
            $this->setGatewayBuilderData();
            $this->paymentGatewayResultData = $this->ifthenpayGateway->execute(
                $this->paymentDefaultData->paymentMethod,
                $this->gatewayBuilder,
                strval($this->paymentDefaultData->order->id),
                strval($this->paymentDefaultData->order->getOrdersTotalPaid())
            )->getData();
            $this->saveToDatabase();
            $this->setSmartyVariables(false);
        }
        return $this;
    }
}
