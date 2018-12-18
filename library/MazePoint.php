<?php
namespace library;

/**
 * 迷宫节点
 * Class MazePoint
 * @package library
 */
class MazePoint
{
    public $x;
    public $y;
    public $z;

    public function __construct($arr)
    {
        $this->x = $arr[0];
        $this->y = $arr[1];
        $this->z = $arr[2];
    }

    /**
     * 获取上下左右的所有节点
     * @return array
     */
    private function getNextArr()
    {
        return [
            new MazePoint([$this->x + 1, $this->y, $this->z]),
            new MazePoint([$this->x, $this->y + 1, $this->z]),
            new MazePoint([$this->x, $this->y, $this->z + 1]),
            new MazePoint([$this->x - 1, $this->y, $this->z]),
            new MazePoint([$this->x, $this->y - 1, $this->z]),
            new MazePoint([$this->x, $this->y, $this->z - 1]),
        ];
    }

    public function findNeighborsPoint()
    {
        $arr = $this->getNextArr();
        // 过滤越界的值 包括"大于最大值 小于最小值"的值
        $arr = array_filter($arr, function ($value) {
            if ($value->x > Maze::$row || $value->y > Maze::$col || $value->z > Maze::$height) {
                return false;
            } elseif ($value->x < 0 || $value->y <0 || $value->z < 0) {
                return false;
            }
            return true;
        });
        return $arr;
    }

    /**
     * 判断传入的节点是否就是当前节点
     * @param MazePoint $point
     * @return bool
     */
    public function equals(MazePoint $point)
    {
        if ($this->x == $point->x && $this->y == $point->y && $this->z == $point->z) {
            return true;
        }
        return false;
    }

    /**
     * 输出可视化的结果
     * @return string
     */
    public function out()
    {
        return "({$this->x},{$this->y},{$this->z})";
    }
}
