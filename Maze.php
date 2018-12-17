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
        $arr = array_filter($arr, function($value){
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
    public function equals(MazePoint $point) {
        if ($this->x == $point->x && $this->y == $point->y && $this->z == $point->z) {
            return true;
        }
        return false;
    }

    public function out() {
        return "({$this->x}{$this->y}{$this->z})";
    }
}

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

/**
 * 迷宫树
 * Class MazeTree
 * @package library
 */
class MazeTree
{
    private $head;

    /**
     * 设置根节点
     * @param MazePoint $point
     */
    public function setHead(MazePoint $point)
    {
        $this->head = new MazeTreeNode($point);
    }

    /**
     * @return mixed
     */
    public function getHead()
    {
        return $this->head->data;
    }

    /**
     * 添加新的节点
     * @param MazePoint|null $parent
     * @param MazePoint $child
     */
    public function addNode($parent, MazePoint $child) {
        if ($parent === null) {
            $this->setHead($child);
        } else {
            $parentTreeNode = $this->findPoint($parent);
            if (!empty($parentTreeNode)) {
                $parentTreeNode->child[] = new MazeTreeNode($child, $parent);
            }
        }
    }

    /**
     * 查找迷宫通路中是否已经包含这个节点
     * @param MazePoint $point
     * @return MazeTreeNode|null
     */
    public function findPoint(MazePoint $point)
    {
        $data = $this->_recursiveFindPoint($this->head, $point);    // 从根节点搜索节点是否存在
        return empty($data) ? NULL : $data;
    }

    /**
     * 递归查找节点是否存在，存在则返回节点，否则返回null
     * @param MazeTreeNode|null $point
     * @param MazePoint $need
     * @return MazeTreeNode|null
     */
    private function _recursiveFindPoint($point, MazePoint $need) {
        $data = null;
        if ($point == null) {
            return $data;
        }
        if ($point->data->equals($need)) {
            $data = &$point;
        } elseif (!empty($point->child)) {
            foreach ($point->child as $oneChild) {
                $data = $this->_recursiveFindPoint($oneChild, $need);
                if (!empty($data)) {
                    break;
                }
            }
        }
        return $data;
    }
}

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

/**
 *  Class Maze
 * 迷宫
 * 1--从随机选择数组中随机选择一个迷宫格作为起点，如果一开始随机数组没有数据，表示刚开始，取起点
 * 2--查找邻近迷宫格
 *   |--如果邻近迷宫格不在迷宫树中，随机选1个迷宫格打穿，将打穿的迷宫格加入到随机选择数组中 （有6个邻居两个打穿？）
 *   |--将已经属于通路的节点从未访问数组中移除（判断迷宫是否完全生成）
 * @package library
 */
class Maze
{
    public static $row;
    public static $col;
    public static $height;

    /**
     * @var MazePoint $startPoint
     */
    public $startPoint;

    /**
     * @var MazePoint $endPoint
     */
    public $endPoint;

    /**
     * 所有节点列表
     * @var array
     */
    public $PointArray;

    /**
     * @var array
     */
    public $visitedList;

    /**
     * 随机数组 邻居节点
     * @var array
     */
    public $neighborArray;

    /**
     * @var MazePointInfo $currentNode
     */
    public $currentNode;

    /**
     * 迷宫通路树
     * @var MazeTree
     */
    public $Tree;

    public function __construct()
    {
        $this->PointArray = [];     // 所有节点池
        $this->visitedList = [];    // 已访问的节点
        $this->neighborArray = [];  // 邻居节点
        $this->Tree = new MazeTree();
    }

    /**
     * @param int $row
     * @param int $col
     * @param int $height
     * @param array $startPoint
     * @param array $endPoint
     */
    public function init($row, $col, $height, $startPoint, $endPoint)
    {
        /**
         * 初始化长宽高
         */
        self::$row = $row-1;
        self::$col = $col-1;
        self::$height = $height-1;

        // 初始化起始和结束节点
        $this->startPoint = new MazePoint($startPoint);
        $this->endPoint = new MazePoint($endPoint);

        // 初始化 尚未访问的全部节点
        for ($i = 0; $i <= self::$row; $i++) {
            for ($j = 0; $j <= self::$row; $j++) {
                for ($k = 0; $k <= self::$row; $k++) {
                    $this->PointArray["node_$i$j$k"] = new MazePointInfo(new MazePoint([$i, $j, $k]));
                }
            }
        }

        // 初始化随机数组
        $x = rand(0, self::$row);
        $y = rand(0, self::$col);
        $z = rand(0, self::$height);
//        $this->currentNode = $this->PointArray["node_$x$y$z"];        // 更改起始生成点
        $this->currentNode = $this->PointArray["node_000"];
        $this->currentNode->isVisited = true;
        $this->Tree->setHead($this->currentNode->data);     // 根节点

        // 将这个点放入已访问的列表中
        array_push($this->visitedList, $this->currentNode);
    }

    /**
     * 检查节点是否已经访问过
     * @param MazePoint $point
     * @return bool
     */
    public function pointVisitedInPointArray(MazePoint $point) {
        $x = $point->x;
        $y = $point->y;
        $z = $point->z;
        $index = "node_{$x}{$y}{$z}";
        /**
         * @var MazePointInfo $pointInfo
         */
        $pointInfo = $this->PointArray[$index];
        return $pointInfo->isVisited;
    }

    /**
     * 设置节点已经访问过
     * @param $point
     */
    public function setVisitPointArray($point)
    {
        $x = $point->x;
        $y = $point->y;
        $z = $point->z;
        $index = "node_{$x}{$y}{$z}";
        $this->PointArray[$index]->isVisited = true;
    }

    /**
     * 检查节点是否在已经访问过的列表中
     * @param MazePoint $point
     * @param bool $delete
     * @return mixed|null
     */
    public function pointInVisitList(MazePoint $point, $delete = false)
    {
        $data = null;
        /**
         * @var MazePointInfo $node
         */
        foreach ($this->visitedList as $key => $node) {
            if ($node->data->equals($point) && $node->isVisited == true) {
                $data = $this->visitedList[$key];
                if ($delete) {
                    unset($this->visitedList[$key]);
                    $this->visitedList = array_values($this->visitedList);
                }
                return $data;
            }
        }
        return $data;
    }

    /**
     * 获取当前正在访问的节点的邻居节点，要求没有越界、并且没有被访问过
     */
    public function getAllowedNeighbor() {
        // 重置邻居节点数组
        $this->neighborArray = [];
        // 查找当前节点的所有没有越界的合法邻居
        $neighborArray = $this->currentNode->data->findNeighborsPoint();
        // 查找没有被访问过的合法邻居
        foreach ($neighborArray as $key => $point) {
            $find = $this->pointInVisitList($point);
            $visited = $this->pointVisitedInPointArray($point);
            if ($find == null && $visited == false) {
                $node = new MazePointInfo($point);
                $node->isVisited = true;
                array_push($this->neighborArray, $node);    // 两个条件都合法的邻居才能拿来作为随机数组
            }
        }
    }

    /**
     * 在这些格子中随机选择一个没有在访问列表中的格子，
     * 如果找到，则添加叶子节点到树中，并且把选中的节点做当前正在访问的节点
     * 否则，从已访问的列表中，随机选取一个作为当前访问的格子
     * @return bool
     */
    public function iteration() {
        if (count($this->neighborArray) !== 0) {
            $neighborRand = $this->neighborArray[array_rand($this->neighborArray)];   // 随机取出一个邻居作为正在访问的节点
            $this->Tree->addNode($this->currentNode->data, $neighborRand->data);  // 连接叶子节点和父节点
            $neighborRand->isVisited = true;
            if (!$this->pointInVisitList($neighborRand->data)) {      // 访问过的节点不用多次进入
                array_push($this->visitedList, $neighborRand);
            }
            $this->currentNode = $neighborRand;
        } else {
            if (count($this->visitedList) == 0) {       // 没有了
                return true;
            }else {
                $rand = rand(0, count($this->visitedList)-1);
                $this->currentNode = $this->visitedList[$rand];       // 随机挑选一个访问过的节点
            }

            if (!$this->currentNode) {
                return true;
            }
            $this->currentNode->isVisited = true;
            $this->pointInVisitList($this->currentNode->data, true);        // 删除节点，避免多次访问到
            $this->setVisitPointArray($this->currentNode->data);
        }
        return false;
    }

    /**
     * 执行
     */
    public function run() {
        while($this->currentNode->isVisited) {
            $this->getAllowedNeighbor();
            $break = $this->iteration();
            if($break) {
                break;
            }
        }
        // todo 修改为到达终点就结束，剩余未访问的节点用于连接周边回环
    }

    /**
     * debug 函数
     */
    public function getNodeChild() {
        $arr = [];
        for ($i = 0; $i <= self::$row; $i++) {
            for ($j = 0; $j <= self::$row; $j++) {
                for ($k = 0; $k <= self::$row; $k++) {
                    $node = $this->Tree->findPoint(new MazePoint([$i, $j, $k]));
                    foreach ($node->child as $child) {
                        $arr["$i$j$k"][] = $child->data;
                    }
                }
            }
        }
        var_dump($arr);
    }

    /**
     * 过滤根节点到起点和终点相同的部分，然后拼接成一条完整的地图路线
     * @param $start
     * @param $end
     * @return array
     */
    private function filterEscapeRoutes($start, $end)
    {
        $index = 0;
        $middlePoint = null;
        while($index < count($start) && $index < count($end)) {
            if($start[$index]->data->equals($end[$index]->data)) {
                $middlePoint = array_shift($start);
                array_shift($start);
            }
            $index ++;
        }

        if(!empty($middlePoint)) {
            $end[] = $middlePoint;
        }

        $end = array_reverse($end);
        return array_merge($start,$end);
    }

    /**
     * 逃生路线
     */
    public function escapeRoutes()
    {
        $arr = [];
        $arr2 = [];
        $endPoint = $this->Tree->findPoint($this->endPoint);
        while ($endPoint->parent !== NULL) {
            $arr[]  = $endPoint;
            $endPoint = $this->Tree->findPoint($endPoint->parent);
        }

        $startPoint = $this->Tree->findPoint($this->startPoint);
        while ($startPoint->parent !== NULL) {
            $arr2[]  = $startPoint;
            $startPoint = $this->Tree->findPoint($startPoint->parent);
        }

        $arr = $this->filterEscapeRoutes($arr2, $arr);  // 合并起点和终点的线路，并删除到根节点的公共部分
        echo "used:".count($arr)."\tunused:" .(((Maze::$row+1) * (Maze::$col+1) * (Maze::$height+1))-count($arr)).PHP_EOL;

        $tmp = [];
        /**
         * @var $mazeTreeNode MazeTreeNode
         */
        foreach ($arr as $mazeTreeNode) {
            $tmp[] = "(" . $mazeTreeNode->data->x  . $mazeTreeNode->data->y  . $mazeTreeNode->data->z . ")";
        }

        echo implode('->',$tmp).PHP_EOL;

        $str = "";
        $tmp = [];
        /**
         * @var $mazeTreeNode MazeTreeNode
         */
        foreach ($arr as $key => $mazeTreeNode) {
            if (($key +1) < count($arr)) {
                $data = $mazeTreeNode->data;
                $dataNext = $arr[$key + 1]->data;
                if ($dataNext->x > $data->x) {
                   $tmp[] = "右";
                } elseif ($dataNext->x < $data->x) {
                    $tmp[] = "左";
                } elseif ($dataNext->y > $data->y) {
                    $tmp[] = "前";
                } elseif ($dataNext->y < $data->y) {
                    $tmp[] = "后";
                } elseif ($dataNext->z > $data->z) {
                    $tmp[] = "上";
                } elseif ($dataNext->z < $data->z) {
                    $tmp[] = "下";
                }
            }
        }
        echo implode('->',$tmp);
    }
}

$maze = new Maze();
$maze->init(10, 10, 10, [0,0,0], [9,9,9]);
//$maze->init(20, 20, 20, [0,0,0], [19,19,19]);
//$maze->init(5, 5, 5, [0,0,0], [4,4,4]);
//$maze->init(3, 3, 3, [0,0,0], [2, 2, 2]);
$maze->run();
$maze->escapeRoutes();
$maze->getNodeChild();