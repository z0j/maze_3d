<?php

use library\Maze;

include_once(__DIR__."/../library/MazePoint.php");
include_once(__DIR__."/../library/MazePointInfo.php");
include_once(__DIR__."/../library/MazeTreeNode.php");
include_once(__DIR__."/../library/MazeTree.php");
include_once(__DIR__."/../library/Maze.php");

$maze = new Maze();
$maze->init(10, 10, 10, [0,0,0], [9,9,9]);
//$maze->init(20, 20, 20, [0,0,0], [19,19,19]);
//$maze->init(5, 5, 5, [0,0,0], [4,4,4]);
//$maze->init(3, 3, 3, [0,0,0], [2, 2, 2]);
$maze->run();
$data = $maze->escapeRoutes();  // 获取逃生路线
$maze->getThreeViewLog(__DIR__."/../data/maze.log");    // 生成三视图
echo $data['allPoint'].PHP_EOL;
echo $data['allPoint1'];
