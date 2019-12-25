<?php
declare(strict_types=1);

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Model;
use App\Utils\Common;
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
     *
     * [字段 => 值, 字段 => 值 ...]
     */
    public $condition = [];

    /**
     * 查询数据
     * @var array
     * 用法：
     * 单表（如果连表查单表数据）：
     * ['*']
     * ['字段', ...]
     * [
     *    表名 => ['字段', ....],
     * ]
     * 多表：
     * [
     *    主表 => ['字段', ....],
     *    其他表 => ['字段', ....]
     * ]
     */
    public $select = ['*'];

    /**
     * 排序
     * @var string|array
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

    /**
     * BaseService constructor.
     */
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
        $this->groupBy = [];
        $this->data = [];
    }

    /**
     * @param RequestInterface $request
     * @param int $type  1：单表查询分页，2：多表查询分页
     * @return \Hyperf\Contract\PaginatorInterface|mixed
     */
    public function index(RequestInterface $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $page = $page < 1 ? 1 : $page;
            $limit = $limit > 100 ? 100 : $limit;

            $pagination = $this->getListByPage((int) $page, (int) $limit);

            foreach ($pagination['data'] as $key => &$value) {
                $value['created_at'] = $value['created_at'] ? date('Y-m-d H:i:s', $value['created_at']) : '';
                $value['updated_at'] = $value['updated_at'] ? date('Y-m-d H:i:s', $value['updated_at']) : '';
            }
            return $pagination;

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
            $data = $this->multiTableJoinQueryBuilder()->first();
            return (array)$data ?? [];

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
            $res = $this->multiTableJoinQueryBuilder()->delete();
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
    public function update(RequestInterface $request)
    {
        try {
            if (!$this->table || !($this->table instanceof Model)) {
                throw new BusinessException(ErrorCode::SERVER_ERROR);
            }
            $res = Db::table($this->table->getTable())->where($this->condition)->update($this->data);
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
    public function store(RequestInterface $request)
    {
        try {
            if (!$this->table || !($this->table instanceof Model)) {
                throw new BusinessException(ErrorCode::SERVER_ERROR);
            }
            $res = Db::table($this->table->getTable())->insertGetId($this->data);
            $this->resetAttributes();
            return $res;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    public function insert(RequestInterface $request)
    {
        try {
            if (!$this->table || !($this->table instanceof Model)) {
                throw new BusinessException(ErrorCode::SERVER_ERROR);
            }
            $res = Db::table($this->table->getTable())->insert($this->data);
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
    public function getListByPage(int $page, int $limit)
    {
        $query = $this->multiTableJoinQueryBuilder();
        $count = $query->count();
        $pagination = $query->paginate($limit, $this->select, 'page', $page)->toArray();
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
     * 单多表关联查询构造器
     * @return \Hyperf\Database\Query\Builder
     */
    public function multiTableJoinQueryBuilder()
    {
        if (!$this->table || !($this->table instanceof Model)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR);
        }
        $query = Db::table($this->table->getTable());

        if (is_array($this->joinTables) && !empty($this->joinTables)) {
            array_walk($this->joinTables, function (&$item) use (&$query) {
                $key = array_search($item, $this->joinTables);
                if (count($item) === 3) {
                    $query = $query->leftJoin($key, $item[0], $item[1], $item[2]);
                }
            });

            if (is_array($this->select) && !empty($this->select)) {
                $arrCount = Common::getArrCountRecursive($this->select);
                $select = [];
                if ($arrCount === 1) {
                    array_walk($this->select, function ($item) use (&$select) {
                       if ($this->table instanceof Model) {
                        $select[] = $this->table->getTable() . '.' . $item;
                       }
                    });
                } else {
                    foreach ($this->select as $key => $value) {
                        if (is_array($value)) {
                            array_walk($value, function ($item) use ($key, &$select) {
                                $select[] = $key . '.' . $item;
                            });
                        } else {
                            $select[] = $key . '.' . $value;
                        }
                    }
                    $select = !empty($select) ? $select : $this->select;
                }
                $query = $query->select($select);
            }
        } else {
            $query = $query->select($this->select);
        }
        if (!empty($this->condition)) {
            $query = $query->where($this->condition);
        }
        if (is_array($this->orderBy) && !empty($this->orderBy)) {
            $orderBy = [];
            foreach ($this->orderBy as $key => $value) {
                if (is_array($value)) {
                    $orderKey = array_keys($value);
                    foreach ($orderKey as $k => $v) {
                        $orderBy[] = env('DB_PREFIX', 'mq_') . "{$key}.{$v} {$value[$v]}";
                    }
                }
            }
            $orderBy = !empty($orderBy) ? implode(',', $orderBy) : $this->orderBy;
            $query = $query->orderByRaw($orderBy);
        } else {
            $orderBy = $this->orderBy;
            $query = $query->orderByRaw($orderBy);
        }

        if (!empty($this->groupBy)) {
            $query = $query->groupBy(implode(',', $this->groupBy));
        }
        $this->resetAttributes();
        return $query;
    }

    /**
     * 构建单表多条件查询
     * @param $searchForm
     * @return array
     */
    public function multiSingleTableSearchCondition($searchForm)
    {
        $type = $searchForm ? $searchForm['type'] : '';
        $keyword = $searchForm ? $searchForm['keyword'] : '';
        $condition = [];
        $tableAttributes = $this->table->getFillable();
        if ($keyword && in_array($type, $tableAttributes)) {
            $condition[] = [$type, 'like', "%{$keyword}%"];
        }
        $searchKeys = array_intersect(array_keys($searchForm), $tableAttributes);
        if (!empty($searchKeys)) {
            array_walk($searchKeys, function ($item) use (&$condition, $searchForm) {
                if (isset($searchForm[$item]) && $searchForm[$item] !== '') {
                    array_push($condition, [$item, '=', $searchForm[$item]]);
                }
            });
        }
        return $condition;
    }
}