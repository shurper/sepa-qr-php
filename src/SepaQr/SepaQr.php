<?php
namespace SepaQr;

use Endroid\QrCode\QrCode;

class SepaQr extends QrCode
{
    const UTF_8 = 1;
    const ISO8859_1 = 2;
    const ISO8859_2 = 3;
    const ISO8859_4 = 4;
    const ISO8859_5 = 5;
    const ISO8859_7 = 6;
    const ISO8859_10 = 7;
    const ISO8859_15 = 8;

    private $sepaValues = array(
        'serviceTag' => 'BCD',
        'version' => 2,
        'characterSet' => 1,
        'identification' => 'SCT'
    );

    public function __construct($text = '')
    {
        parent::__construct($text);

        $this->setErrorCorrection(self::LEVEL_MEDIUM);
    }

    public function setServiceTag($serviceTag = 'BCD')
    {
        if ($serviceTag !== 'BCD') {
            throw new SepaQrException('Invalid service tag');
        }

        $this->sepaValues['serviceTag'] = $serviceTag;

        return $this;
    }

    public function setVersion($version = 2)
    {
        if (!in_array($version, array(1, 2))) {
            throw new SepaQrException('Invalid version');
        }

        $this->sepaValues['version'] = $version;

        return $this;
    }

    public function setCharacterSet($characterSet = strictlyPHP_SepaQr::UTF_8)
    {
        $this->sepaValues['characterSet'] = $characterSet;
        return $this;
    }

    public function setIdentification($identification = 'SCT')
    {
        if ($identification !== 'SCT') {
            throw new SepaQrException('Invalid identification code');
        }

        $this->sepaValues['identification'] = $identification;

        return $this;
    }

    public function setBic($bic)
    {
        $this->sepaValues['bic'] = $bic;
        return $this;
    }

    public function setName($name)
    {
        $this->sepaValues['name'] = $name;
        return $this;
    }

    public function setIban($iban)
    {
        $this->sepaValues['iban'] = $iban;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->sepaValues['amount'] = $amount;
        return $this;
    }

    public function setPurpose($purpose)
    {
        $this->sepaValues['purpose'] = $purpose;
        return $this;
    }

    public function setRemittanceReference($remittanceReference)
    {
        $this->sepaValues['remittanceReference'] = $remittanceReference;
        return $this;
    }

    public function setRemittanceText($remittanceText)
    {
        $this->sepaValues['remittanceText'] = $remittanceText;
        return $this;
    }

    public function setInformation($information)
    {
        $this->sepaValues['information'] = $information;
        return $this;
    }

    public function validateSepaValues($values)
    {
        if ($values['version'] === 1 && !$values['bic']) {
            throw new SepaQrException('Missing BIC of the beneficiary bank');
        }

        if ($values['bic']) {
            if (strlen($values['bic']) < 8) {
                throw new SepaQrException('BIC of the beneficiary bank cannot be shorter than 8 characters');
            }

            if (strlen($values['bic']) > 11) {
                throw new SepaQrException('BIC of the beneficiary bank cannot be longer than 11 characters');
            }
        }

        if (!$values['name']) {
            throw new SepaQrException('Missing name of the beneficiary');
        }

        if (strlen($values['name']) > 70) {
            throw new SepaQrException('Name of the beneficiary cannot be longer than 70 characters');
        }

        if (!$values['iban']) {
            throw new SepaQrException('Missing account number of the beneficiary');
        }

        if (strlen($values['iban']) > 34) {
            throw new SepaQrException('Account number of the beneficiary cannot be longer than 34 characters');
        }

        if ($values['amount']) {
            if ($values['amount'] < 0.01) {
                throw new SepaQrException('Amount of the credit transfer cannot be smaller than 0.01 Euro');
            }

            if ($values['amount'] > 999999999.99) {
                throw new SepaQrException('Amount of the credit transfer cannot be higher than 999999999.99 Euro');
            }
        }

        if ($values['remittanceReference'] && strlen($values['remittanceReference']) > 35) {
            throw new SepaQrException('Structured remittance information cannot be longer than 35 characters');
        }

        if ($values['remittanceText'] && strlen($values['remittanceText']) > 140) {
            throw new SepaQrException('Unstructured remittance information cannot be longer than 140 characters');
        }

        if ($values['information'] && strlen($values['information']) > 70) {
            throw new SepaQrException('Beneficiary to originator information cannot be longer than 70 characters');
        }
    }

    public function create()
    {
        $defaults = array(
            'bic' => '',
            'name' => '',
            'purpose' => '',
            'remittanceReference' => '',
            'remittanceText' => '',
            'information' => ''
        );

        $values = array_merge($defaults, $this->sepaValues);

        $this->validateSepaValues($values);

        $this->setText(implode("\n", array(
            $values['serviceTag'],
            sprintf('%03d', $values['version']),
            $values['characterSet'],
            $values['identification'],
            $values['bic'],
            $values['name'],
            $values['iban'],
            sprintf('EUR%.2f', $values['amount']),
            $values['purpose'],
            $values['remittanceReference'],
            $values['information']
        )));

        return parent::create();
    }
}