<?php
/**
 * Created by PhpStorm.
 * User: StefanWarnat
 * Date: 26.03.2019
 * Time: 17:54.
 */

namespace Workflow\BPMN;

class Element
{
    public const TYPE_STARTEVENT = 'startEvent';
    public const TYPE_TASK = 'task';
    public const TYPE_EXCLUSIVEGATEWAY = 'exclusiveGateway';

    private $id;

    private $name;

    private $type;

    /**
     * @var Element[]
     */
    private $outgoing = [];

    /**
     * @var Element[]
     */
    private $incoming = [];

    private $ownRow = 0;

    private $nextChildRow = 0;

    private $nextChildColumn = 0;

    private $ownColumn = 0;

    private $currentX = 0;

    private $currentY = 0;

    private $currentWidth = 0;

    private $currentHeight = 0;

    private $outputIndex = 0;

    /**
     * Height, Width.
     */
    private $sizes = [
        self::TYPE_EXCLUSIVEGATEWAY => [0.5, 0.8334],
        self::TYPE_STARTEVENT => [0.3, 0.5],
        self::TYPE_TASK => [1, 1],
    ];

    private $input = [
        self::TYPE_EXCLUSIVEGATEWAY => [1.2, 0.5],
        self::TYPE_STARTEVENT => [0, 0.5],
        self::TYPE_TASK => [0, 0.5],
    ];

    /**
     * @var Element
     */
    private $parent;

    private $maxChildCount = 1;

    public function __construct($id, $name, $type, $row)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;

        $this->setRow($row);
    }

    public function setOutputIndex($outputIndex)
    {
        $this->outputIndex = intval($outputIndex);
    }

    public function getOutputIndex()
    {
        return $this->outputIndex;
    }

    public function setCurrentColumn($column)
    {
        $this->ownColumn = $column;
    }

    public function getCurrentColumn()
    {
        return $this->ownColumn;
    }

    public function getCurrentRow()
    {
        return $this->ownRow;
    }

    public function getChildCount()
    {
        $currentChild = 0;
        if (empty($this->outgoing)) {
            return 0;
        }

        foreach ($this->outgoing as $outgoings) {
            foreach ($outgoings as $outgoing) {
                $tmpChildCount = max($outgoing->getChildCount(), 1);

                $currentChild += $tmpChildCount;
            }
        }

        return $currentChild;
    }

    public function setRow($rowNumber)
    {
        $this->ownRow = intval($rowNumber);
        $this->nextChildRow = $this->ownRow;
        $this->nextChildColumn = $this->ownColumn + 1;
    }

    /*
    public function updateChilds() {

        if($this->maxChildCount < $childCount) {
            $this->maxChildCount = $childCount;

            if($this->parent !== null) {
                $this->parent->adjustMaxChild($childCount);
            }
        }
    }
    */

    public function setParent(Element &$parent)
    {
        $this->parent = &$parent;
    }

    public function addOutgoing($childObj, $outputIndex)
    {
        if (!isset($this->outgoing[$outputIndex])) {
            $this->outgoing[$outputIndex] = [];
        }

        $this->outgoing[$outputIndex][] = $childObj;
    }

    public function addIncoming($childObj)
    {
        $this->incoming[] = $childObj;
    }

    public function addChild(Element $child, $outputIndex = 1, $doMoveBlock = true)
    {
        $currentChildCount = $this->getChildCount();
        // if($child->getId() == 'Block_551' && $doMoveBlock == true) var_dump($currentChildCount);

        if ($doMoveBlock === true) {
            $child->setRow($this->ownRow + max($currentChildCount, 0));
            $child->setCurrentColumn($this->ownColumn + 1);
        }

        $this->nextChildRow += max($currentChildCount, 1);

        $child->setParent($this);

        $child->addIncoming($this);
        $this->addOutgoing($child, $outputIndex);

        Flow::getId($this->getId(), $child->id);
        Flow::setOutputIndex($this->id, $child->getId(), $outputIndex);
        //        $this->nextChildColumn++;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $xml \DOMDocument
     */
    public function addXML($xml)
    {
        $process = $xml->getElementsByTagName('bpmn:process')[0];

        $block = $xml->createElement('bpmn:' . $this->type);
        $block->setAttribute('id', $this->getId());
        $block->setAttribute('name', $this->getName());
        $process->appendChild($block);

        foreach ($this->incoming as $incoming) {
            $flow = $xml->createElement('bpmn:incoming', Flow::getId($incoming->getId(), $this->id));
            $block->appendChild($flow);
        }

        foreach ($this->outgoing as $outgoings) {
            foreach ($outgoings as $outgoing) {
                $flow = $xml->createElement('bpmn:outgoing', Flow::getId($this->id, $outgoing->getId()));
                $block->appendChild($flow);
            }
        }
    }

    /**
     * @param $xml \DOMDocument
     * @param $plane \DOMElement
     */
    public function addPlaneXML($xml, $plane)
    {
        // $plane = $xml->getElementsByTagName('bpmndi:BPMNPlane');

        switch ($this->type) {
            case self::TYPE_EXCLUSIVEGATEWAY:
                $element = $xml->createElement('bpmndi:BPMNShape');
                $element->setAttribute('bpmnElement', $this->id);
                $element->setAttribute('isMarkerVisible', 'true');
                $element->setAttribute('id', $this->id . '_di');

                $bounds = $xml->createElement('dc:Bounds');

                $this->currentX = ($this->getCurrentColumn() * 200) + 50;
                $bounds->setAttribute('x', $this->currentX);

                if ($this->sizes[$this->type][1] < 1) {
                    $this->currentY = ($this->getCurrentRow() * 100) + 50 + ((60 * $this->sizes[$this->type][1]) / 2);
                } else {
                    $this->currentY = ($this->getCurrentRow() * 100) + 50;
                }
                $bounds->setAttribute('y', $this->currentY);

                $this->currentWidth = $this->sizes[$this->type][0] * 100;
                $this->currentHeight = $this->sizes[$this->type][1] * 60;

                $bounds->setAttribute('width', $this->currentWidth);
                $bounds->setAttribute('height', $this->currentHeight);

                $element->appendChild($bounds);
                $plane->appendChild($element);

                break;
            case self::TYPE_STARTEVENT:
            case self::TYPE_TASK:
                $element = $xml->createElement('bpmndi:BPMNShape');
                $element->setAttribute('bpmnElement', $this->id);
                $element->setAttribute('id', $this->id . '_di');

                $bounds = $xml->createElement('dc:Bounds');

                $this->currentX = ($this->getCurrentColumn() * 200) + 50;
                $bounds->setAttribute('x', $this->currentX);

                if ($this->sizes[$this->type][1] < 1) {
                    $this->currentY = ($this->getCurrentRow() * 100) + 50 + ((60 * $this->sizes[$this->type][1]) / 2);
                } else {
                    $this->currentY = ($this->getCurrentRow() * 100) + 50;
                }
                $bounds->setAttribute('y', $this->currentY);

                $this->currentWidth = $this->sizes[$this->type][0] * 100;
                $this->currentHeight = $this->sizes[$this->type][1] * 60;

                $bounds->setAttribute('width', $this->currentWidth);
                $bounds->setAttribute('height', $this->currentHeight);

                $element->appendChild($bounds);
                $plane->appendChild($element);
                break;
        }
    }

    public function getSize()
    {
        return $this->sizes[$this->type];
    }

    public function getX()
    {
        return $this->currentX;
    }

    public function getY()
    {
        return $this->currentY;
    }

    public function getHeight()
    {
        return $this->currentHeight;
    }

    public function getWidth()
    {
        return $this->currentWidth;
    }

    public function getOutgoingPosition($outgoingSequence, $maxOutgoingSequences, $index, $ingoing)
    {
        switch ($this->type) {
            case self::TYPE_EXCLUSIVEGATEWAY:
                switch ($index) {
                    case 1:
                        $return = [
                            [
                                'y' => $this->getY() + ($this->currentHeight / 2),
                                'x' => $this->getX() + $this->currentWidth,
                            ],
                        ];

                        break;
                    case 2:
                        $return = [
                            [
                                'y' => $this->getY() + $this->currentHeight,
                                'x' => $this->getX() + ($this->currentWidth / 2),
                            ],
                            [
                                'y' => $ingoing['y'],
                                'x' => $this->getX() + ($this->currentWidth / 2),
                            ],
                        ];
                        break;
                    case 3:
                        $return = [
                            [
                                'y' => $this->getY() - 10,
                                'x' => $this->getX() + ($this->currentWidth / 2),
                            ],
                        ];
                        break;
                }

                return $return;
                break;
            case self::TYPE_STARTEVENT:
                $y = $this->getY() + ($this->currentHeight / 2);
                $x = $this->getX() + ($this->currentWidth / 2);

                return [['x' => $x, 'y' => $y]];
                break;
            case self::TYPE_TASK:
                $heightPerSequence = $this->currentHeight / $maxOutgoingSequences;

                $x = $this->getX() + $this->currentWidth;

                // $heightPerSequence split the complete height in equal parts
                // The Top Position will added by all previous part heights and by half of own part height
                $y = $this->getY() + ($heightPerSequence * ($outgoingSequence - 1)) + ($heightPerSequence / 2);

                /*
                                if($maxOutgoingSequences = 1) {
                                    $y = $this->getY() + ($this->currentHeight / 2);
                                } else {

                                }
                */
                return [['x' => $x, 'y' => $y]];
                break;
        }
    }

    public function getIngoingPosition()
    {
        switch ($this->type) {
            case self::TYPE_EXCLUSIVEGATEWAY:
                $y = $this->getY() + ($this->currentHeight / 2);
                $x = $this->getX();

                return ['x' => $x, 'y' => $y];
                break;
            case self::TYPE_STARTEVENT:
                $y = $this->getY() + ($this->currentHeight / 2);
                $x = $this->getX();

                return ['x' => $x, 'y' => $y];
                break;
            case self::TYPE_TASK:
                $y = $this->getY() + ($this->currentHeight / 2);
                $x = $this->getX();

                return ['x' => $x, 'y' => $y];
                break;
        }
    }
}
