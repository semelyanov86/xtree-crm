<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class PDFMaker_PayBySquare_Helper extends Vtiger_Base_Model
{
    /**
     * @var string[]
     */
    public $paymentParams = [
        'iban',
        'amount',
        'currency',
        'vs',
        'ss',
        'cs',
        'note',
        'due_date',
        'swift',
        'size',
    ];

    /**
     * @return self
     */
    public static function getInstance($iban = '', $amount = '')
    {
        $self = new self();
        $self->setIBAN($iban);
        $self->setAmount($amount);
        $self->set('size', 200);
        $self->set('currency', 'EUR');

        return $self;
    }

    /**
     * @return array|string|string[]|null
     */
    public static function getNumber($value)
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * @return array|string|string[]|null
     */
    public static function getUppercaseAZ09($value)
    {
        return preg_replace('/[^A-Z0-9]/', '', $value);
    }

    public static function generateQrCode($value)
    {
        include_once 'modules/PDFMaker/resources/phpqrcode/qrlib.php';

        QRcode::png($value);
    }

    /**
     * @return string|void
     */
    public static function generatePaymentInfo($iban, $amount, $data)
    {
        $paymentData = implode("\t", [
            0 => '',
            1 => '1',
            2 => implode("\t", [
                true,
                $amount,                        // SUMA
                $data['currency'],                        // JEDNOTKA
                $data['due_date'],                    // DATUM
                $data['vs'],                    // VARIABILNY SYMBOL
                $data['cs'],                        // KONSTANTNY SYMBOL
                $data['ss'],                        // SPECIFICKY SYMBOL
                '',
                $data['note'],                    // POZNAMKA
                '1',
                $iban,    // IBAN
                $data['swift'],                    // SWIFT
                '0',
                '0',
            ]),
        ]);
        $paymentData = strrev(hash('crc32b', $paymentData, true)) . $paymentData;
        $descriptor = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $proc = proc_open("/usr/bin/xz '--format=raw' '--lzma1=lc=3,lp=0,pb=2,dict=128KiB' '-c' '-'", $descriptor, $pipes);

        fwrite($pipes[0], $paymentData);
        fclose($pipes[0]);

        $content1 = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $content2 = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        if (!empty($content2)) {
            exit($content2);
        }

        proc_close($proc);

        $paymentData = bin2hex("\x00\x00" . pack('v', strlen($paymentData)) . $content1);
        $b = '';

        for ($i = 0; $i < strlen($paymentData); ++$i) {
            $b .= str_pad(base_convert($paymentData[$i], 16, 2), 4, '0', STR_PAD_LEFT);
        }

        $l = strlen($b);
        $r = $l % 5;

        if ($r > 0) {
            $p = 5 - $r;
            $b .= str_repeat('0', $p);
            $l += $p;
        }

        $l = $l / 5;
        $paymentData = str_repeat('_', $l);

        for ($i = 0; $i < $l; ++$i) {
            $paymentData[$i] = '0123456789ABCDEFGHIJKLMNOPQRSTUV'[bindec(substr($b, $i * 5, 5))];
        }

        return $paymentData;
    }

    public function convertFromArray($data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->paymentParams)) {
                $this->set($key, $value);
            }
        }
    }

    public function setIBAN($value)
    {
        $this->set('iban', $value);
    }

    public function setAmount($value)
    {
        $this->set('amount', $value);
    }

    /**
     * @return string
     */
    public function getImage()
    {
        [$width, $height] = explode('x', $this->getSize());

        return sprintf('<img width="%s" src="%s" alt="payBySquare">', $width . 'px', $this->getLink());
    }

    /**
     * @return string|null
     */
    public function getPaymentInfo()
    {
        $amount = floatval($this->getAmount());
        $iban = PDFMaker_PayBySquare_Helper::getUppercaseAZ09($this->getIBAN());
        $currency = $this->getCurrency();
        $variableSymbol = $this->getVariableSymbol();
        $specialSymbol = $this->getSpecialSymbol();
        $constantSymbol = $this->getConstantSymbol();
        $dueDate = $this->getDueDate();
        $note = $this->getNote();
        $swift = $this->getSwift();
        $data = [
            'currency' => isset($currency) ? preg_replace(
                '/[^A-Z]/',
                '',
                $currency,
            ) : 'EUR',
            'due_date' => !empty($dueDate) ? PDFMaker_PayBySquare_Helper::getNumber($dueDate) : '',
            'vs' => !empty($variableSymbol) ? PDFMaker_PayBySquare_Helper::getNumber($variableSymbol) : '',
            'cs' => !empty($constantSymbol) ? PDFMaker_PayBySquare_Helper::getNumber($constantSymbol) : '',
            'ss' => !empty($specialSymbol) ? PDFMaker_PayBySquare_Helper::getNumber($specialSymbol) : '',
            'note' => !empty($note) ? strip_tags($note) : '',
            'swift' => !empty($swift) ? PDFMaker_PayBySquare_Helper::getUppercaseAZ09($swift) : '',
        ];

        return self::generatePaymentInfo($iban, $amount, $data);
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $paymentInfo = $this->getPaymentInfo();
        [$width, $height] = explode('x', $this->getSize());

        return '<barcode code="' . $paymentInfo . '" type="QR" class="barcode" style="border: 0; width: ' . $width . '; height:' . $height . '; " />';
    }

    /**
     * @return string
     */
    public function getLink()
    {
        include_once 'modules/PDFMaker/resources/phpqrcode/qrlib.php';

        global $site_URL;
        $paymentInfo = $this->getPaymentInfo();
        $fileName = decideFilePath() . 'paybysquare_' . time() . '.png';

        QRcode::png($paymentInfo, $fileName);

        return rtrim($site_URL, '/') . '/' . $fileName;
    }

    /**
     * @return Value|null
     */
    public function getIBAN()
    {
        return $this->get('iban');
    }

    /**
     * @return Value|null
     */
    public function getAmount()
    {
        return $this->get('amount');
    }

    /**
     * @return Value|null
     */
    public function getCurrency()
    {
        return $this->get('currency');
    }

    /**
     * @return Value|null
     */
    public function getVariableSymbol()
    {
        return $this->get('vs');
    }

    /**
     * @return Value|null
     */
    public function getSpecialSymbol()
    {
        return $this->get('ss');
    }

    /**
     * @return Value|null
     */
    public function getConstantSymbol()
    {
        return $this->get('cs');
    }

    /**
     * @return Value|null
     */
    public function getNote()
    {
        return $this->get('note');
    }

    /**
     * @return Value|null
     */
    public function getDueDate()
    {
        return $this->get('due_date');
    }

    /**
     * @return Value|null
     */
    public function getSwift()
    {
        return $this->get('swift');
    }

    /**
     * @return Value|null
     */
    public function getSize()
    {
        return $this->get('size');
    }
}
