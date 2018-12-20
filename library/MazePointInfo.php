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

    /**
     * 计算一个迷宫格的索引
     * 可以传入MazePoint类型的point 或者包含xyz坐标信息的数组 数组格式为[x, y, z]
     * @param MazePoint|array z$point
     * @return string
     */
    public static function generalIndex($point)
    {
        if (is_array($point)) {
            $x = $point[0];
            $y = $point[1];
            $z = $point[2];
        } else {
            $x = $point->x;
            $y = $point->y;
            $z = $point->z;
        }
        $index = "node_{$x}{$y}{$z}";
        return $index;
    }

    /**
     * 返回一个包含多个pointInfo的数组
     * @param $row
     * @param $col
     * @param $height
     * @return array
     */
    public static function initInfoArray($row, $col, $height)
    {
        $PointInfoArr = [];
        for ($i = 0; $i <= $row; $i++) {
            for ($j = 0; $j <= $col; $j++) {
                for ($k = 0; $k <= $height; $k++) {
                    $point = new MazePoint([$i, $j, $k]);
                    $index = self::generalIndex($point); // 计算状态数组的index
                    $PointInfoArr[$index] = new self($point);
                }
            }
        }
        return $PointInfoArr;
    }
}
