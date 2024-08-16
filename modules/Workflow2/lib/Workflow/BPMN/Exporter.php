<?php
/**
 * Created by PhpStorm.
 * User: StefanWarnat
 * Date: 26.03.2019
 * Time: 17:18.
 */

namespace Workflow\BPMN;

use Workflow\Main;
use Workflow\Manager;
use Workflow\Task;
use Workflow\VtUtils;

class Exporter
{
    /**
     * @var Main
     */
    private $workflowObj;

    /**
     * @var Task
     */
    private $startBlock;

    /**
     * @var Task
     */
    private $currentBlock;

    private $taskArray = [];

    /**
     * @var Element
     */
    private $lastElement;

    /**
     * @var Element[]
     */
    private $elements = [];

    private $connections = [];

    private $currentRow = 0;

    private $currentColumn = 0;

    private $latestOutputIndex = 1;

    private $globalLaneCounter = 0;

    /**
     * @var \DOMDocument
     */
    private $xml;

    public function __construct(Main $workflowObj)
    {
        $this->workflowObj = $workflowObj;

        $sql = 'SELECT id FROM vtiger_wfp_blocks WHERE workflow_id = ' . $this->workflowObj->getId() . " AND type='start' LIMIT 1";
        $row = VtUtils::fetchByAssoc($sql);

        $this->currentBlock = $this->startBlock = Manager::getTaskHandler('start', $row['id'], $this->workflowObj);

        $this->currentRow = 0;
        $this->currentColumn = 0;

        $this->addBlock($this->currentBlock);
    }

    public function addBlock(Task $task, ?Element $parent = null, $outputIndex = 1)
    {
        if ($task->getBlockId() == $this->startBlock->getBlockId()) {
            $type = Element::TYPE_STARTEVENT;
        } else {
            $type = Element::TYPE_TASK;
        }

        if (isset($this->elements['Block_' . $task->getBlockId()])) {
            $parent->addChild($this->elements['Block_' . $task->getBlockId()], $outputIndex, false);

            return;
        }

        $block = new Element(
            'Block_' . $task->getBlockId(),
            $task->getTitle(),
            $type,
            $this->currentRow,
        );

        $block->setCurrentColumn($this->currentColumn);

        // $this->currentColumn += 1; //$size[1];

        $this->elements['Block_' . $task->getBlockId()] = $block;

        if ($parent !== null) {
            $this->connections[] = [
                'from' => $parent->getId(),
                'to' => $block->getId(),
                'outputIndex' => $outputIndex,
            ];

            $parent->addChild($block, $outputIndex);
        }

        $parent = $block;

        $sql = 'SELECT output FROM vtiger_wf_types WHERE type = ?';
        $data = VtUtils::fetchByAssoc($sql, [$task->getType()]);
        $outputs = VtUtils::json_decode(html_entity_decode($data['output']));

        $outputCount = [];
        foreach ($outputs as $output) {
            $nextBlocks = $task->getNextTasks([$output[0]]);

            if (!empty($nextBlocks)) {
                $outputCount[$output[0]] = $nextBlocks;
            }
        }

        $this->latestOutputIndex = 1;

        if (count($outputCount) > 1) {
            $this->elements['ExclusiveGateway_Block_' . $task->getBlockId()] = new Element(
                'ExclusiveGateway_Block_' . $task->getBlockId(),
                '',
                Element::TYPE_EXCLUSIVEGATEWAY,
                $this->currentRow,
            );

            $block->addChild($this->elements['ExclusiveGateway_Block_' . $task->getBlockId()]);

            // $block->setCurrentColumn($this->currentColumn);

            $parent = $this->elements['ExclusiveGateway_Block_' . $task->getBlockId()];

            $this->connections[] = [
                'from' => 'Block_' . $task->getBlockId(),
                'to' => 'ExclusiveGateway_Block_' . $task->getBlockId(),
                'outputIndex' => $outputIndex,
                'waypoints' => '',
            ];
        }

        $currentRow = $this->currentRow;
        $outputIndex = 0;
        foreach ($outputCount as $outputKey => $blocks) {
            // $this->currentColumn = $parent->getCurrentRow() + 1;
            // $this->currentRow = $currentRow + ($counter++);
            ++$outputIndex;
            foreach ($blocks as $singleBlock) {
                $this->addBlock($singleBlock, $parent, $outputIndex);
            }
        }
        $currentRow = $this->currentRow;
    }

    public function getXML()
    {
        /*
        echo '<pre>';
        foreach($this->elements as $elementId => $element) {
            echo $elementId.': '.$element->getCurrentRow().','.$element->getCurrentColumn().PHP_EOL;
        }
        exit();
        */
        $workflowObj = new \Workflow2();
        $this->xml = new \DOMDocument();
        $this->xml->encoding = 'UTF-8';
        //        $this->xml->preserveWhiteSpace = false;

        $definitions = $this->xml->createElement('bpmn:definitions');
        $definitions->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $definitions->setAttribute('xmlns:bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');
        $definitions->setAttribute('xmlns:bpmndi', 'http://www.omg.org/spec/BPMN/20100524/DI');
        $definitions->setAttribute('xmlns:dc', 'http://www.omg.org/spec/DD/20100524/DC');
        $definitions->setAttribute('xmlns:di', 'http://www.omg.org/spec/DD/20100524/DI');
        $definitions->setAttribute('id', 'Definitions_' . $this->workflowObj->getId() . '');
        $definitions->setAttribute('targetNamespace', 'http://bpmn.io/schema/bpmn');
        $definitions->setAttribute('exporter', 'Redoo Networks Workflow Designer');
        $definitions->setAttribute('exporterVersion', $workflowObj->getVersion());
        $this->xml->appendChild($definitions);

        $this->xml->formatOutput = true;

        $map = $this->xml->documentElement;

        $process = $this->xml->createElement('bpmn:process');
        $process->setAttribute('id', 'Workflow_' . $this->workflowObj->getId());
        $process->setAttribute('isExecutable', 'false');
        $map->appendChild($process);

        foreach ($this->elements as $block) {
            $block->addXML($this->xml);
        }

        $flows = Flow::getAll();
        foreach ($flows as $outgoingId => $childFlows) {
            foreach ($childFlows as $incomingId => $id) {
                $block = $this->xml->createElement('bpmn:sequenceFlow');
                $block->setAttribute('id', $id['id']);
                $block->setAttribute('sourceRef', $outgoingId);
                $block->setAttribute('targetRef', $incomingId);

                $process->appendChild($block);
            }
        }

        $diagram = $this->xml->createElement('bpmndi:BPMNDiagram');
        $diagram->setAttribute('id', 'Diagram_' . $this->workflowObj->getId());

        $plane = $this->xml->createElement('bpmndi:BPMNPlane');
        $plane->setAttribute('id', 'Plane_1');
        $plane->setAttribute('bpmnElement', 'Workflow_' . $this->workflowObj->getId());
        $diagram->appendChild($plane);

        $definitions->appendChild($diagram);

        $positions = [];
        foreach ($this->elements as $id => $element) {
            $element->addPlaneXML($this->xml, $plane);
        }

        $flows = Flow::getAll();

        foreach ($flows as $outgoingId => $childFlows) {
            $outgoingCounter = 0;
            $laneCounter = 0;
            foreach ($childFlows as $incomingId => $flow) {
                ++$outgoingCounter;
                $block = $this->xml->createElement('bpmndi:BPMNEdge');
                $block->setAttribute('id', $flow['id'] . '_di');
                $block->setAttribute('bpmnElement', $flow['id']);
                $source = $this->elements[$outgoingId];
                $target = $this->elements[$incomingId];

                $point = [];

                $sourceColumn = $source->getCurrentColumn();
                $targetColumn = $target->getCurrentColumn();

                $ingoing = $target->getIngoingPosition();
                $outgoing = $source->getOutgoingPosition($outgoingCounter, count($childFlows), $flow['outputIndex'], $ingoing);

                if ($targetColumn > $sourceColumn) {
                    // Gerade linie
                    foreach ($outgoing as $point) {
                        $newPoint = $this->xml->createElement('di:waypoint');
                        $newPoint->setAttribute('x', $point['x']);
                        $newPoint->setAttribute('y', $point['y']);
                        $block->appendChild($newPoint);

                        $lastY = $point['y'];
                        $lastX = $point['x'];
                    }

                    if ($ingoing['y'] == $lastY) {
                        $newPoint = $this->xml->createElement('di:waypoint');
                        $newPoint->setAttribute('x', $ingoing['x']);
                        $newPoint->setAttribute('y', $ingoing['y']);
                        $block->appendChild($newPoint);
                    } else {
                        $xSwitch = $lastX + (($ingoing['x'] - $lastX) / 2);

                        $newPoint = $this->xml->createElement('di:waypoint');
                        $newPoint->setAttribute('x', $xSwitch);
                        $newPoint->setAttribute('y', $lastY);
                        $block->appendChild($newPoint);

                        $newPoint = $this->xml->createElement('di:waypoint');
                        $newPoint->setAttribute('x', $xSwitch);
                        $newPoint->setAttribute('y', $ingoing['y']);
                        $block->appendChild($newPoint);

                        $newPoint = $this->xml->createElement('di:waypoint');
                        $newPoint->setAttribute('x', $ingoing['x']);
                        $newPoint->setAttribute('y', $ingoing['y']);
                        $block->appendChild($newPoint);
                    }
                } else {
                    // Nächstes Element liegt in Diagramm weiter vorn -> Pfad zurück

                    foreach ($outgoing as $point) {
                        $newPoint = $this->xml->createElement('di:waypoint');
                        $newPoint->setAttribute('x', $point['x']);
                        $newPoint->setAttribute('y', $point['y']);
                        $block->appendChild($newPoint);

                        $lastY = $point['y'];
                        $lastX = $point['x'];
                    }

                    $newPoint = $this->xml->createElement('di:waypoint');
                    $newPoint->setAttribute('x', $lastX + 20);
                    $newPoint->setAttribute('y', $lastY);
                    $block->appendChild($newPoint);

                    $laneY = $source->getY() - 20 + (5 * $laneCounter++);

                    $newPoint = $this->xml->createElement('di:waypoint');
                    $newPoint->setAttribute('x', $lastX + 20);
                    $newPoint->setAttribute('y', $laneY);
                    $block->appendChild($newPoint);

                    $newPoint = $this->xml->createElement('di:waypoint');
                    $newPoint->setAttribute('x', $ingoing['x'] - 20);
                    $newPoint->setAttribute('y', $laneY);
                    $block->appendChild($newPoint);

                    $newPoint = $this->xml->createElement('di:waypoint');
                    $newPoint->setAttribute('x', $ingoing['x'] - 20);
                    $newPoint->setAttribute('y', $ingoing['y']);
                    $block->appendChild($newPoint);

                    $newPoint = $this->xml->createElement('di:waypoint');
                    $newPoint->setAttribute('x', $ingoing['x']);
                    $newPoint->setAttribute('y', $ingoing['y']);
                    $block->appendChild($newPoint);
                }

                $plane->appendChild($block);
            }
        }

        return $this->xml->saveXML();
    }

    public function getNextTasks() {}

    private function calculatePositions()
    {
        //        $positions = $this->startBlock;
    }
}
