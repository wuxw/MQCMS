<?php
declare(strict_types=1);

namespace App\Service;


use App\Exception\BusinessException;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Paginator\Paginator;

class BaseService
{
    /**
     * @var string
     */
    public $table = '';

    /**
     * join表查询参数
     * @var array
     * 用法：
     * [
     *    join表名 => [主表.字段, '=', join表名.字段],
     *    join表名 => [主表.字段, '=', join表名.字段],
     *    join表名 => [主表.字段, '=', join表名.字段],
     * ]
     */
    public $joinTables = [];

    /**
     * 查询条件
     * @var array
     * 用法：
     * [
     *    [表名.字段, '=', 值],
     *    [表名.字段, '=', 值],
     * ]
     */
    public $condition = [];

    /**
     * 查询数据
     * @var array
     */
    public $select = ['*'];

    /**
     * 排序
     * @var string
     * 用法：
     * 1、单表排序的格式是字符串 "字段 DESC/ASC"
     * 2、多表排序的格式是数据
     * [
     *    表名 => [字段 => 'DESC/ASC'],
     *    表名 => [字段 => 'DESC/ASC'],
     * ]
     */
    public $orderBy = 'id desc';

    /**
     * 分组
     * @var array
     * 用法：
     * [字段，字段...]
     */
    public $groupBy = [];

    /**
     * 存储数组
     * @var array
     */
    public $data = [];

    public function __construct()
    {
        $this->resetAttributes();
    }

    /**
     * 重置属性值
     */
    public function resetAttributes()
    {
        $this->joinTables = [];
        $this->condition = [];
        $this->select = ['*'];
        $this->orderBy = 'id desc';
        $this->groupBy = '';
        $this->data = [];
    }

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public function index(RequestInterface $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $page = $page < 1 ? 1 : $page;
            $limit = $limit > 100 ? 100 : $limit;

            $data = self::getListByPage($this->table, (int) $page, (int) $limit, $this->condition, $this->select, $this->orderBy, $this->groupBy);
            $this->resetAttributes();
            return $data;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function show(RequestInterface $request)
    {
        try {
            $data = Db::table($this->table)->where($this->condition)->select($this->select)->first();
            $this->resetAttributes();
            return $data ?? [];

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * @param RequestInterface $request
     * @param $data
     * @return int
     */
    public function update(RequestInterface $request)
    {
        try {
            $res = Db::table($this->table)->where($this->condition)->update($this->data);
            $this->resetAttributes();
            return $res;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * @param RequestInterface $request
     * @return int
     */
    public function delete(RequestInterface $request)
    {
        try {
            $res = Db::table($this->table)->where($this->condition)->delete();
            $this->resetAttributes();
            return $res;
        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * @param RequestInterface $request
     * @param $data
     * @return int
     */
    public function store(RequestInterface $request)
    {
        try {
            $res = Db::table($this->table)->insertGetId($this->data);
            $this->resetAttributes();
            return $res;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 根据查询结果获取分页列表
     * @param string $table
     * @param int $limit
     * @param array $condition
     * @return \Hyperf\Contract\PaginatorInterface
     */
    public static function getListByPage(string $table, int $page, int $limit, array $condition, array $select, string $order_by, array $group_by)
    {
        $query = Db::table($table);
        if (!empty($condition)) {
            $query = $query->where($condition);
        }
        if (!empty($select)) {
            $query = $query->select($select);
        }
        if ($order_by) {
            $query = $query->orderByRaw($order_by);
        }
        if (!empty($group_by)) {
            $query = $query->groupBy(implode(',', $group_by));
        }
        $count = $query->count();
        $pagination = $query->paginate($limit, $select, 'page', $page)->toArray();
        $pagination['total'] = $count;
        return $pagination;
    }

    /**
     * 根据结果数组分页
     * @param $data
     * @param $per_page
     * @param $current_page
     * @return Paginator
     */
    public static function lists($data, $per_page, $current_page)
    {
        return new Paginator($data, $per_page, $current_page);
    }

    /**
     * 多表关联查询构造器
     * @return \Hyperf\Database\Query\Builder
     */
    public function multiTableJoinQueryBuilder()
    {
        $query = Db::table($this->table);
        if (!empty($this->joinTables)) {
            array_walk($this->joinTables, function (&$item) use (&$query) {
                $key = array_search($item, $this->joinTables);
                $query = $query->leftJoin($key, $item[0], $item[1], $item[2]);
            });
        }
        if (!empty($this->condition)) {
            $query = $query->where($this->condition);
        }
        if (!empty($this->select)) {
            $query = $query->select($this->select);
        }
        if (is_array($this->orderBy) && !empty($this->orderBy)) {
            $orderBy = [];
            foreach ($this->orderBy as $key => $value) {
                $orderKey = array_keys($value);
                foreach ($orderKey as $k => $v) {
                    $orderBy[$key] = env('DB_PREFIX') . "{$key}.{$v} {$value[$v]}";
                }
            }
            $orderBy = implode(',', $orderBy);
        } else {
            $orderBy = $this->orderBy;
        }
        $query = $query->orderByRaw($orderBy);
        if (!empty($this->groupBy)) {
            $query = $query->groupBy(implode(',', $this->groupBy));
        }
        $this->resetAttributes();
        return $query;
    }

}