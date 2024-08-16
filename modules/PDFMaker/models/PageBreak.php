<?php
/* * *******************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class PDFMaker_PageBreak_Model
{
    public const PAGE_BREAK_TAG = '#pagebreak#';

    /**
     * @var bool|simple_html_dom
     */
    protected $content = false;

    /**
     * @var string
     */
    protected $pageBreak = '<pagebreak>';

    /**
     * @param string $value
     * @return self
     */
    public static function getInstance($value)
    {
        $self = new self();
        $self->setContent($value);

        return $self;
    }

    public function updateContent()
    {
        $this->update();
        $this->replaceInTables();
        $this->replace();
    }

    public function replaceInTables()
    {
        $htmlDom = $this->getHtmlDom();

        foreach ($htmlDom->find('table tr') as $trTag) {
            $this->cloneRows($trTag);
        }

        $this->setContent($htmlDom->save());
    }

    public function replace()
    {
        $this->setContent(str_replace(self::PAGE_BREAK_TAG, $this->getPageBreak(), $this->getContent()));
    }

    public function update()
    {
        $this->setContent(str_replace(strtoupper(self::PAGE_BREAK_TAG), self::PAGE_BREAK_TAG, $this->getContent()));
    }

    /**
     * @return simple_html_dom
     */
    public function getHtmlDom()
    {
        PDFMaker_PDFMaker_Model::getSimpleHtmlDomFile();

        return str_get_html($this->getContent());
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $value
     */
    public function setContent($value)
    {
        $this->content = $value;
    }

    /**
     * @param simple_html_dom_node $trTag
     * @return array
     */
    public function getRowMap($trTag)
    {
        $tdNum = 0;
        $trChildren = 0;
        $trMap = [];

        while ($tdTag = $trTag->children($trChildren)) {
            ++$trChildren;
            ++$tdNum;

            foreach (explode(self::PAGE_BREAK_TAG, $tdTag->innertext) as $trNum => $tdContent) {
                ++$trNum;
                $trMap['tr_' . $trNum]['td_' . $tdNum] = $tdContent;
            }
        }

        return $trMap;
    }

    /**
     * @param simple_html_dom_node $trTag
     */
    public function cloneRows($trTag)
    {
        $trMap = $this->getRowMap($trTag);
        $trClones = $this->getRowClones($trTag, $trMap);
        $trImplode = $this->getImplodeText($trTag);

        $this->setOuterText($trTag, implode($trImplode, $trClones));
    }

    /**
     * @param simple_html_dom_node $trTag
     * @param array $trMap
     * @return array
     */
    public function getRowClones($trTag, $trMap)
    {
        $trClones = [];

        foreach ($trMap as $key => $tdValues) {
            $trClone = $trTag;
            $tdCloneNum = 0;
            $tdChildren = 0;

            while ($tdClone = $trClone->children($tdChildren)) {
                ++$tdChildren;
                ++$tdCloneNum;

                if ($tdValues['td_' . $tdCloneNum]) {
                    $tdClone->innertext = $tdValues['td_' . $tdCloneNum];
                } else {
                    $tdClone->innertext = '';
                }
            }

            $trClones[] = $trClone->outertext;
        }

        return $trClones;
    }

    /**
     * @param simple_html_dom_node $trTag
     * @return string
     */
    public function getImplodeText($trTag)
    {
        $first = clone $trTag->parent();
        $first->innertext = '#explode#';
        $firstTags = explode('#explode#', $first->outertext);
        $result = $firstTags[1];

        if ($first->tag !== 'table') {
            $second = clone $first->parent();
            $second->innertext = '#explode#';
            $secondTags = explode('#explode#', $second->outertext);

            $result .= $secondTags[1];
            $result .= $secondTags[0];
            $result .= $this->getPageBreak();
        } else {
            $result .= $this->getPageBreak();
        }

        $result .= $firstTags[1];

        return $result;
    }

    /**
     * @return string
     */
    public function getPageBreak()
    {
        return $this->pageBreak;
    }

    /**
     * @param string $value
     */
    public function setPageBreak($value)
    {
        $this->pageBreak = $value;
    }

    /**
     * @param simple_html_dom_node $trTag
     * @param string $value
     */
    public function setOuterText($trTag, $value)
    {
        $trTag->outertext = $value;
    }
}
