<?php
namespace library;

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
     * @param int $row 宽
     * @param int $col 长
     * @param int $height 高
     * @param array $startPoint 起点数组，不能越界
     * @param array $endPoint 终点数组，不能越界
     * @param bool $randRootNode 是否使用随机的根节点
     */
    public function init($row, $col, $height, $startPoint, $endPoint, $randRootNode = false)
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
        $this->PointArray = MazePointInfo::initInfoArray(self::$row, self::$col, self::$height);

        // 初始化随机根节点 或起点作为根节点
        $param = ($randRootNode) ? [rand(0, self::$row), rand(0, self::$col), rand(0, self::$height)] : $startPoint;
        $index = MazePointInfo::generalIndex($param);
        $this->currentNode = $this->PointArray[$index];        // 更改起始生成点

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
    public function pointVisitedInPointArray(MazePoint $point)
    {
        $index = MazePointInfo::generalIndex($point);
        /**
         * @var MazePointInfo $pointInfo
         */
        $pointInfo = $this->PointArray[$index];
        return $pointInfo->isVisited;
    }

    /**
     * 设置节点已经访问过
     * @param MazePoint $point
     */
    public function setVisitPointArray(MazePoint $point)
    {
        $index = MazePointInfo::generalIndex($point);
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
    public function getAllowedNeighbor()
    {
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
    public function iteration()
    {
        if (count($this->neighborArray) !== 0) {
            $neighborRand = $this->neighborArray[array_rand($this->neighborArray)];   // 随机取出一个邻居作为正在访问的节点
            $this->Tree->addNode($this->currentNode->data, $neighborRand->data);  // 连接叶子节点和父节点
            $neighborRand->isVisited = true;
            array_push($this->visitedList, $neighborRand);
            $this->currentNode = $neighborRand;
        } else {
            if (count($this->visitedList) == 0) {       // 没有了
                return true;
            } else {
                $rand = rand(0, count($this->visitedList)-1);
                $this->currentNode = $this->visitedList[$rand];       // 随机挑选一个访问过的节点
                if (!$this->currentNode) {
                    return true;
                }
            }

            $this->currentNode->isVisited = true;
            $this->pointInVisitList($this->currentNode->data, true);        // 删除节点，避免多次访问到
            $this->setVisitPointArray($this->currentNode->data);                    // 设置所有节点表里面的是否已经被访问过
        }
        return false;
    }

    /**
     * 执行
     */
    public function run()
    {
        while ($this->currentNode->isVisited) {
            $this->getAllowedNeighbor();
            $break = $this->iteration();
            if ($break) {
                break;
            }
        }
        // todo 修改为到达终点就结束，剩余未访问的节点用于连接周边回环
    }

    /**
     * debug 函数
     */
    public function getNodeChild()
    {
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
        $startTmp = $start; // 不能同时求index的情况下还对这个数组进行操作
        $endTmp = $end;
        while ($index < count($startTmp) && $index < count($endTmp)) {
            if ($startTmp[$index]->data->equals($endTmp[$index]->data)) {    // 如果有共同节点，保留最后一个共同点作为起点和终点的父节点
                $middlePoint = $startTmp[$index];
                array_shift($start);
                array_shift($start);
            }
            $index ++;
        }

        if (!empty($middlePoint)) {
            $end[] = $middlePoint;
        }

        $end = array_reverse($end);
        return array_merge($start, $end);
    }

    /**
     * 逃生路线
     */
    public function escapeRoutes()
    {
        $res = [];
        $endToRootLine = [];        // 终点到根节点节点数组
        $startToRootLine = [];      // 起点到根节点数组
        $endPoint = $this->Tree->findPoint($this->endPoint);
        while ($endPoint->parent !== null) {
            $endToRootLine[]  = $endPoint;
            $endPoint = $this->Tree->findPoint($endPoint->parent);
        }

        $startPoint = $this->Tree->findPoint($this->startPoint);
        if ($startPoint->parent == null) {  // 起点就根节点
            $startToRootLine[] = $startPoint;
        } else {                            // 起点不是根节点
            while ($startPoint->parent !== null) {
                $startToRootLine[]  = $startPoint;
                $startPoint = $this->Tree->findPoint($startPoint->parent);
            }
        }

        $arr = $this->filterEscapeRoutes($startToRootLine, $endToRootLine);  // 合并起点和终点的线路，并删除到根节点的公共部分
        $res['used'] = count($arr);
        $res['unused'] = ((Maze::$row+1) * (Maze::$col+1) * (Maze::$height+1))-$res['used'];

        $tmp = [];
        /**
         * @var $mazeTreeNode MazeTreeNode
         */
        foreach ($arr as $mazeTreeNode) {
            $tmp[] = "(" . $mazeTreeNode->data->x  . $mazeTreeNode->data->y  . $mazeTreeNode->data->z . ")";
        }
        $res['allPoint'] = implode('->', $tmp);

        $tmp = [];
        $tmp2 = [];
        /**
         * @var $mazeTreeNode MazeTreeNode
         */
        foreach ($arr as $key => $mazeTreeNode) {
            if (($key +1) < count($arr)) {
                $data = $mazeTreeNode->data;
                $dataNext = $arr[$key + 1]->data;
                if ($dataNext->x > $data->x) {
                    $tmp[] = "右";
                    $tmp2[] = 'right';
                } elseif ($dataNext->x < $data->x) {
                    $tmp[] = "左";
                    $tmp2[] = 'left';
                } elseif ($dataNext->y > $data->y) {
                    $tmp[] = "前";
                    $tmp2[] = 'front';
                } elseif ($dataNext->y < $data->y) {
                    $tmp[] = "后";
                    $tmp2[] = 'back';
                } elseif ($dataNext->z > $data->z) {
                    $tmp[] = "上";
                    $tmp2[] = 'up';
                } elseif ($dataNext->z < $data->z) {
                    $tmp[] = "下";
                    $tmp2[] = 'down';
                }
            }
        }

        $res['allPoint1'] = implode('->', $tmp);
        $res['allPoint2'] = implode('->', $tmp2);

        return $res;
    }

    /**
     * 获取三视图
     */
    public function getThreeViewLog($path = null)
    {
        $result = "";
        $path = empty($path) ? __DIR__."/../data/maze.log" : $path;

        // 判断前后两个节点中间的墙是不是通的
        $func = function ($thisPoint = [], $nextPoint = []) {
            /**
             * @var MazeTreeNode $point
             * @var MazeTreeNode $upThisPoint
             */
            $point = $this->Tree->findPoint(new MazePoint($thisPoint));       // 本层节点
            $upThisPoint = $this->Tree->findPoint(new MazePoint($nextPoint));  // 下一层节点
            // 如果下一层的父节点是该节点，则该节点到下一层的通路
            // 如果下一层的父节点是该节点，则该节点到下一层的通路
            if (!empty($point->parent) && !empty($upThisPoint) && $point->parent->equals($upThisPoint->data)) {       // 下穿上
                return "☐";
            } elseif (!empty($upThisPoint->parent) && !empty($point->parent) && $upThisPoint->parent->equals($point->data)) { // 上穿下
                return "☐";
            }
            return "☒";
        };

        // 添加底部索引
        $funcGetIndex = function ($count) {
            $lineIndex = [" "];
            for ($l = 0; $l <= $count; $l++) {
                $lineIndex[] = $l%10;
            }
            return implode(' ', $lineIndex);
        };

        // 写入逃生路线
        $result .= var_export($this->escapeRoutes(), true).PHP_EOL;

        // 分类别写入三视图
        $result .= '上视图：'.PHP_EOL;
        for ($i = 0; $i <= (Maze::$height - 1); $i ++) {      // z 轴 第9层和第10层之间，所以到8就好了
            $result .= "---------------第 " . ($i+1) . " 面墙-------------------".PHP_EOL;
            $surface = [];  // 整面状态
            for ($j = Maze::$col; $j >= 0; $j --) {  // y 轴
                $line = [];
                for ($k = 0; $k <= Maze::$row; $k ++) { // x轴
                    $line[] = $func([$k, $j, $i], [$k, $j, $i + 1]);
                }
                $surface[] = implode(' ', $line);
            }
            $str = implode("\r\n", $surface);
            $result .= $str.PHP_EOL;
        }
        $result .= "===========================================================".PHP_EOL;



        $result .= '正视图：'.PHP_EOL;
        for ($i = 0; $i <= (Maze::$col - 1); $i ++) {      // y轴 第9层和第10层之间，所以到8就好了
            $result .= "---------------第 " . ($i+1) . " 面墙-------------------".PHP_EOL;
            $surface = [];
            for ($j = Maze::$height; $j >= 0; $j --) {  // z 轴
                $line = [];
                for ($k = 0; $k <= Maze::$row; $k ++) { // x轴
                    $line[] = $func([$k, $i, $j], [$k, $i+1, $j]);
                }
                $surface[] = implode(' ', $line);
            }
            $str = implode("\r\n", $surface);
            $result .= $str.PHP_EOL;
        }
        $result .= "===========================================================".PHP_EOL;


        $result .= '右视图：'.PHP_EOL;
        for ($i = 0; $i <= (Maze::$row - 1); $i ++) {      // x轴 第9层和第10层之间，所以到8就好了
            $result .= "---------------第 " . ($i+1) . " 面墙-------------------".PHP_EOL;
            $surface = [];
            for ($j = Maze::$height; $j >= 0; $j --) {  // z 轴
                $line = [];
                for ($k = 0; $k <= Maze::$col; $k ++) { // y轴
                    $line[] = $func([$i, $k, $j], [$i+1, $k, $j]);
                }
                $surface[] = implode(' ', $line);
            }
            $str = implode("\r\n", $surface);
            $result .= $str.PHP_EOL;
        }
        $result .= "===========================================================".PHP_EOL;

        file_put_contents($path, $result);
    }
}
