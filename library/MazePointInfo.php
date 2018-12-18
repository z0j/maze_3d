<?php
namespace library;

/**
 * 所有节点列表，包含是否已经访问过属性
 * Class MazePointInfo
 * @package library
 */
class MazePointInfo
{
    /**
     * @var bool $isVisited
     */
    public $isVisited;

    /**
     * @var MazePoint $data
     */
    public $data;

    /**
     * MazePointInfo constructor.
     * @param MazePoint $data
     */
    public function __construct($data)
    {
        $this->isVisited = false;
        $this->data = $data;
    }
}
