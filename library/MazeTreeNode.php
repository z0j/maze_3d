<?php
namespace library;

/**
 * 迷宫树的节点
 * Class MazeTreeNode
 * @package library
 */
class MazeTreeNode
{
    public $parent;

    /**
     * @var MazePoint
     */
    public $data;

    /**
     * @var array
     */
    public $child = [];

    public function __construct($data, $parent = null)
    {
        $this->data = $data;
        if ($parent !== null) {
            $this->parent = $parent;
        }
    }
}


