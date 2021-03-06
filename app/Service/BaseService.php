<?php
declare(strict_types=1);

namespace App\Service;

use App\Constants\ErrorCode;
use App\Exception\BusinessException;
use App\Model\Model;
use App\Utils\Common;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Paginator\Paginator;

class BaseService
{
    /**
     * @var string
     */
    public $model = '';

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
     * 关联模型使用以下方式：
     * [
     *    关联模型名称1 => ['字段', ....],
     *    关联模型名称2 => ['字段', ....],
     * ]
     * 多变join方式：
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
     * Relations
     * @var array
     * 用法：
     * [relationsName1，relationsName2...]
     */
    public $with = [];

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
        $this->with = [];
    }

    /**
     * 获取分页列表
     * @param RequestInterface $request
     * @return \Hyperf\Contract\PaginatorInterface|mixed
     */
    public function index(RequestInterface $request)
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $page < 1 && $page = 1;
            $limit > 100 && $limit = 100;

            $pagination = $this->getListByPage((int) $page, (int) $limit);
            foreach ($pagination['data'] as $key => &$value) {
                $value['created_at'] && $value['created_at'] = date('Y-m-d H:i:s', (int) $value['created_at']);
                $value['updated_at'] && $value['updated_at'] = date('Y-m-d H:i:s', (int) $value['updated_at']);
            }
            return $pagination;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 展示详情
     * @param RequestInterface $request
     * @return \Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function show(RequestInterface $request)
    {
        try {
            $info = $this->multiTableJoinQueryBuilder()->first()->toArray();
            if ($info) {
                $info['created_at'] && $info['created_at'] = date('Y-m-d H:i:s', (int) $info['created_at']);
                $info['updated_at'] && $info['updated_at'] = date('Y-m-d H:i:s', (int) $info['updated_at']);
            }
            return $info;

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 删除
     * @param RequestInterface $request
     * @return int
     */
    public function delete(RequestInterface $request)
    {
        try {
            return $this->multiTableJoinQueryBuilder()->delete();

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 更新
     * @param RequestInterface $request
     * @param $data
     * @return int
     */
    public function update(RequestInterface $request)
    {
        try {
            $data = $this->data;
            return $this->multiTableJoinQueryBuilder()->update($data);

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 存储或更新
     * @param RequestInterface $request
     * @return int
     */
    public function store(RequestInterface $request)
    {
        try {
            $data = $this->data;
            if (!empty($this->condition)) {
                $model = $this->multiTableJoinQueryBuilder()->first();
                if (!$model->toArray()) {
                    $model = new $this->model;
                }
            } else {
                $model = new $this->model;
            }
            $tableAttributes = $this->model->getFillable();
            $searchKeys = array_intersect(array_keys($data), $tableAttributes);

            foreach ($searchKeys as $key => $value) {
                $model->$value = $data[$value];
            }
            return $model->save();

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 存储获取最新ID
     * @param RequestInterface $request
     * @return bool
     */
    public function insert(RequestInterface $request)
    {
        try {
            $data = $this->data;
            return $this->multiTableJoinQueryBuilder()->insertGetId($data);

        } catch (\Exception $e) {
            throw new BusinessException((int)$e->getCode(), $e->getMessage());
        }
    }

    /**
     * 根据查询结果获取分页列表
     * @param int $page
     * @param int $limit
     * @return mixed
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
     * 注意：此方法因为在构造完成会重置参数(resetAttributes)，如再次使用condition, select, orderBy等参数，请在构造之前用变量存储
     * @return \Hyperf\Database\Query\Builder
     */
    public function multiTableJoinQueryBuilder()
    {
        if (!$this->model || !($this->model instanceof Model)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR);
        }
        $query = $this->model::query();

        if (!empty($this->with)) {
            $baseSelect = $this->select;
            array_walk($this->with, function ($item) use (&$query, $baseSelect) {
                $query = $query->with([$item => function ($query) use ($item, $baseSelect) {
                    if (isset($baseSelect[$item])) {
                        return $query->select($baseSelect[$item]);
                    }
                }]);
            });
        } else {
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
                            $select[] = $this->model->getTable() . '.' . $item;
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
            $query = $query->orderByRaw($this->orderBy);
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
        if (!$this->model || !($this->model instanceof Model)) {
            throw new BusinessException(ErrorCode::SERVER_ERROR);
        }
        $searchForm = is_array($searchForm) ? $searchForm : json_decode($searchForm, true);
        $type = isset($searchForm['type']) ? $searchForm['type'] : '';
        $keyword = isset($searchForm['keyword']) ? trim($searchForm['keyword']) : '';
        $timeForm = isset($searchForm['time']) ? $searchForm['time'] : [];
        $condition = $this->condition;
        $tableAttributes = $this->model->getFillable();

        if ($keyword && in_array($type, $tableAttributes)) {
            $condition[] = [$this->model->getTable() . '.' . $type, 'like', "%{$keyword}%"];
        }
        $searchKeys = array_intersect(array_keys($searchForm), $tableAttributes);
        if (!empty($searchKeys)) {
            array_walk($searchKeys, function ($item) use (&$condition, $searchForm) {
                if (isset($searchForm[$item]) && $searchForm[$item] !== '') {
                    array_push($condition, [$this->model->getTable() . '.' . $item, '=', $searchForm[$item]]);
                }
            });
        }
        $searchKeys = array_intersect(array_keys($timeForm), $tableAttributes);
        if (!empty($searchKeys)) {
            array_walk($searchKeys, function ($item) use (&$condition, $timeForm) {
                if (isset($timeForm[$item]) && ($timeForm[$item] || !empty($timeForm[$item]))) {
                    if (is_array($timeForm[$item]) && count($timeForm[$item]) === 2) {
                        if ($timeForm[$item][0] !== '' && $timeForm[$item][1] !== '') {
                            array_push($condition, [$this->model->getTable() . '.' . $item, '>=', strtotime($timeForm[$item][0])]);
                            array_push($condition, [$this->model->getTable() . '.' . $item, '<=', strtotime($timeForm[$item][1])]);
                        }
                    } else {
                        array_push($condition, [$this->model->getTable() . '.' . $item, '>=', strtotime($timeForm[$item])]);
                    }
                }
            });
        }
        if (!empty($condition)) {
            $this->condition = $condition;
        }
        return $condition;
    }
}