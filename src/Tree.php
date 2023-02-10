<?php
// +----------------------------------------------------------------------
// | Tree 树形结构数据处理
// +----------------------------------------------------------------------

namespace stapek\tree;

class Tree
{

    /**
     * 原始数据
     * [
     *      1 => ['id'=>'1','parentid'=>0,'name'=>'一级栏目一'],
     *      2 => ['id'=>'2','parentid'=>0,'name'=>'一级栏目二'],
     *      3 => ['id'=>'3','parentid'=>1,'name'=>'二级栏目一'],
     *      4 => ['id'=>'4','parentid'=>1,'name'=>'二级栏目二'],
     *      5 => ['id'=>'5','parentid'=>2,'name'=>'二级栏目三'],
     *      6 => ['id'=>'6','parentid'=>3,'name'=>'三级栏目一'],
     *      7 => ['id'=>'7','parentid'=>3,'name'=>'三级栏目二']
     * ]
     * @var array
     */
    protected $data = [];

    /**
     * 生成树型结构所需修饰符号，可以换成图片
     * @var array
     */
    protected $icon = ['&emsp;│', '&emsp;├', '&emsp;└'];

    /**
     * 分隔符
     * @var string
     */
    protected $nbsp = "&nbsp;";

    /**
     * 处理后数据
     * @var array
     */
    protected $ret = [];

    /**
     * 父节点字段名
     * @var string
     */
    protected $pField = 'parentid';

    /**
     * 下级节点字段名
     * @var string
     */
    protected $uField = 'child';

    /**
     * 名称字段名
     * @var string
     */
    protected $nField = 'name';

    /**
     * 加上间隔符后的字段名
     * @var string
     */
    protected $sField = 'spacer_name';


    /**
     * 构造函数
     * Tree constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->data($data);
    }

    /**
     * 设置原始数据
     * @param array $data
     * @return $this
     */
    public function data($data)
    {
        $this->data = $data;
        $this->ret = [];
        return $this;
    }

    /**
     * 设置修饰符
     * @param array $icons
     * @return $this
     */
    public function icon($icons)
    {
        $this->icon = $icons;
        return $this;
    }

    /**
     *
     * @param string $nbsp
     * @return $this
     */
    public function nbsp($nbsp)
    {
        $this->nbsp = $nbsp;
        return $this;
    }

    /**
     * 设置父节点字段名
     * @param string $field
     * @return $this
     */
    public function pfield($field)
    {
        $this->pField = $field;
        return $this;
    }

    /**
     * 设置下级节点字段名
     * @param string $field
     * @return $this
     */
    public function ufield($field)
    {
        $this->uField = $field;
        return $this;
    }

    /**
     * 设置名称字段名
     * @param string $field
     * @return $this
     */
    public function nfield($field)
    {
        $this->nField = $field;
        return $this;
    }

    /**
     * 设置间隔字段名
     * @param string $field
     * @return $this
     */
    public function sfield($field)
    {
        $this->sField = $field;
        return $this;
    }

    /**
     * 获取处理后的结果
     * @return array
     */
    public function result()
    {
        return $this->ret;
    }

    /**
     * 获取处理结果指定的字段集合
     * @param string $field
     * @param null $k 哪个字段作为键名
     * @return array
     */
    public function resultValue($field, $k = null)
    {
        $ref = [];
        foreach ($this->ret as $id => $rs) {
            if ($k && isset($rs[$k])) {
                $key = $rs[$k];
            } else {
                $key = $id;
            }
            $ref[$key] = isset($rs[$field]) ? $rs[$field] : null;
        }
        return $ref;
    }

    /**
     * 重置结果
     * @return $this
     */
    public function resultReset()
    {
        $this->ret = [];
        return $this;
    }

    /**
     * 得到树型结构（单条）
     * @param int $myid 指定层级ID，默认从0开始
     * @param string $adds
     * @return $this
     */
    public function getTreeOne($myid = 0, $adds = '')
    {
        //初始循环次数
        $number = 1;
        //获取指定层级下的数据
        $child = $this->getChild($myid);
        if (!empty($child) && is_array($child)) {
            //获取总数量
            $total = count($child);
            //遍历数据
            foreach ($child as $id => $data) {
                //检查是否有对应名称字段数据
                if (!isset($data[$this->nField])) {
                    continue;
                }
                $j = $k = '';
                //当前循环次数=总数时代表最后一个
                if ($number == $total) {
                    $j .= $this->icon[2];
                } else {
                    $j .= $this->icon[1];
                    //不想等是附加标识符
                    $k = $adds ? $this->icon[0] : '';
                }
                //修饰符
                $spacer = $adds ? ($adds . $j) : '';
                $data[$this->sField] = $spacer . $data[$this->nField];
                $this->ret[] = $data;
                $nbsp = $this->nbsp;
                $this->getTreeOne($id, $adds . $k . $nbsp);
                $number++;
            }
        }
        return $this;
    }

    /**
     * 得到树型结构数组
     * @param int $myid
     * @return array
     */
    public function getTreeArray($myid = 0)
    {
        $this->resultReset();
        $ret = [];
        //获取指定层级下的数据
        $child = $this->getChild($myid);
        if (!empty($child) && is_array($child)) {
            foreach ($child as $id => $data) {
                $ret[$id] = $data;
                //继续下级
                $ret[$id][$this->uField] = $this->getTreeArray($id);
            }
        }
        return $ret;
    }

    /**
     * 获取某个层级的数组列表
     * @param int $myid 层级
     * @return array
     */
    protected function getChild($myid = 0)
    {
        $ref = [];
        if (!is_array($this->data)) {
            return [];
        }
        foreach ($this->data as $id => $a) {
            if (empty($this->pField) || !isset($a[$this->pField])) {
                continue;
            }
            if ($a[$this->pField] == $myid) {
                $ref[$id] = $a;
            }
        }
        return $ref;
    }

}