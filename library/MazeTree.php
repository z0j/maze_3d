<?php
namespace library;

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
    public function addNode($parent, MazePoint $child)
    {
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
        $data = $this->recursiveFindPoint($this->head, $point);    // 从根节点搜索节点是否存在
        return empty($data) ? null : $data;
    }

    /**
     * 递归查找节点是否存在，存在则返回节点，否则返回null
     * @param MazeTreeNode|null $point
     * @param MazePoint $need
     * @return MazeTreeNode|null
     */
    private function recursiveFindPoint($point, MazePoint $need)
    {
        $data = null;
        if ($point == null) {
            return $data;
        }
        if ($point->data->equals($need)) {
            $data = &$point;
        } elseif (!empty($point->child)) {
            foreach ($point->child as $oneChild) {
                $data = $this->recursiveFindPoint($oneChild, $need);
                if (!empty($data)) {
                    break;
                }
            }
        }
        return $data;
    }
}
