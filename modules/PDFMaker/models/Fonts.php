<?php

class PDFMaker_Fonts_Model extends Vtiger_Base_Model
{
    public $fontdata;

    public static function getInstance()
    {
        $self = new self();
        $self->retrieveFonts();

        return $self;
    }

    public function retrieveFonts()
    {
        require_once 'modules/PDFMaker/resources/mpdf/config_fonts.php';
    }

    public function getFonts()
    {
        return array_keys($this->fontdata);
    }

    public function getFontsString()
    {
        return implode(';', $this->getFonts());
    }

    public function getFontFaces()
    {
        require_once 'modules/PDFMaker/resources/mpdf/config_fonts.php';

        $fontFace = '';
        $url = 'modules/PDFMaker/resources/mpdf/ttfonts/';

        foreach ($this->fontdata as $name => $value) {
            if (isset($value['R'])) {
                $file = $url . $value['R'];
                $fontFace .= $this->getFontFace($name, $file);
            }

            if (isset($value['B'])) {
                $file = $url . $value['B'];
                $fontFace .= $this->getFontFace($name, $file, 'bold');
            }

            if (isset($value['I'])) {
                $file = $url . $value['I'];
                $fontFace .= $this->getFontFace($name, $file, 'normal', 'italic');
            }

            if (isset($value['BI'])) {
                $file = $url . $value['BI'];
                $fontFace .= $this->getFontFace($name, $file, 'bold', 'italic');
            }
        }

        return $fontFace;
    }

    public function isUrlExists($file)
    {
        $file_headers = get_headers($file);

        if (!$file_headers || $file_headers[0] === 'HTTP/1.1 404 Not Found') {
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @param string $file
     * @param string $weight
     * @param string $style
     * @return string
     */
    public function getFontFace($name, $file, $weight = 'normal', $style = 'normal')
    {
        if (!file_exists($file)) {
            return '';
        }

        return sprintf(
            '
@font-face {
    font-family: "%s";
    src: url("%s") format("%s");
    font-weight: %s;
    font-style: %s;
}',
            $name,
            $file,
            $this->getFileExtension($file),
            $weight,
            $style,
        );
    }

    /**
     * @param string $file
     * @return string
     */
    public function getFileExtension($file)
    {
        $extension = strtolower(pathinfo($file)['extension']);
        $types = [
            'ttf' => 'truetype',
            'otf' => 'opentype',
            'eot' => 'embedded-opentype',
        ];

        if (isset($types[$extension])) {
            $extension = $types[$extension];
        }

        return $extension;
    }
}
