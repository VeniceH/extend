<?php
/**
 * Date: 2018/1/24
 * Time: 15:12
 * ClassName: php7 mongo类
 * Desc:
 */
namespace com;

use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\CommandException;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use think\Exception;

class MongoModel
{
    // $config ['hostname', 'password', 'username', 'hostport', 'database', 'db_name', '']
    public function __construct($config)
    {
        $host         = 'mongodb://' . ($config['username'] ? "{$config['username']}" : '') . ($config['password'] ? ":" . urlencode($config['password']) . "@" : '') . $config['hostname'] . ($config['hostport'] ? ":{$config['hostport']}" : '') . '/' . ($config['database'] ? "{$config['database']}" : '');
        $this->config = $config;
        $this->db     = new Manager($host);
    }

    // 清空集合数据
    public function nearRemove($collection)
    {
        $bulk       = new BulkWrite();

        $bulk->delete([]);
        $result = $this->db->executeBulkWrite("{$this->config['db_name']}.{$collection}", $bulk);

        return $result;
    }

    /**
     * 插入数据
     * @param string $collection 集合名称
     * @param array $data 插入数据
     * @param string $existColumn 判断是否存在的字段
     * @return bool|ObjectID
     * @anthor ljh
     */
    public function insert($collection, $data, $existColumn = null)
    {
        $bulk = new BulkWrite();

        if ($existColumn) {

            $bulk->update(
                [$existColumn => $data[$existColumn]],
                ['$set' => $data],
                ['multi' => false, 'upsert' => true]// multi是否更新所有数据（false只更新第一条），upsert不存在数据时是否创建记录
            );
            $result = $this->db->executeBulkWrite("{$this->config['db_name']}.{$collection}", $bulk);

        } else {
            if (!isset($data['_id'])) {
                $id          = new ObjectID();
                $data['_id'] = $id;
            }
            $bulk->insert($data);
            $result = $this->db->executeBulkWrite("{$this->config['db_name']}.{$collection}", $bulk);
        }

        // 成功的话，返回一个MongoDB\Driver\WriteResult实例。
        if (get_class($result) == 'MongoDB\Driver\WriteResult') {
            if (!empty($id)) {
                return $id;
            }
            return true;
        } else {
            return false;
        }
    }

    public function insertAll($collection, $data, $existColumn = null)
    {
        $bulk = new BulkWrite();

        if ($existColumn) {

            foreach ($data as $value) {
                $bulk->update(
                    [$existColumn => $value[$existColumn]],
                    ['$set' => $value],
                    ['multi' => false, 'upsert' => true]// multi是否更新所有数据（false只更新第一条），upsert不存在数据时是否创建记录
                );
            }
            $result = $this->db->executeBulkWrite("{$this->config['db_name']}.{$collection}", $bulk);

        } else {

            foreach ($data as $value) {
                if (!isset($data['_id'])) {
                    $id          = new ObjectID();
                    $data['_id'] = $id;
                }
                $bulk->insert($value);
            }
            $result = $this->db->executeBulkWrite("{$this->config['db_name']}.{$collection}", $bulk);

        }

        // 成功的话，返回一个MongoDB\Driver\WriteResult实例。
        if (get_class($result) == 'MongoDB\Driver\WriteResult') {
            if (!empty($id)) {
                return $id;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 添加索引
     * @param string $collection 集合名
     * @param string $indexName 索引名
     * @param array $indexKeys 索引字段数组
     * @return bool
     * @anthor ljh
     */
    public function addIndex($collection, $indexName, $indexKeys)
    {
        $cmd = [
            'createIndexes' => $collection, //集合名
            'indexes'       => [
                [
                    'name'       => $indexName, //索引名
                    'key'        => $indexKeys, //索引字段数组 $indexKeys  = array( 'name' => 1, 'age' => 1 );
                    'unique'     => false,
                    'background' => true,
                ],
            ],
        ];

        $command  = new Command($cmd);
        $result   = $this->db->executeCommand($this->config['db_name'], $command);
        $response = current($result->toArray());

        if ($response->ok == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function update($collection, $filter, $data)
    {
        $bulk = new BulkWrite();

        $bulk->update(
            $filter,
            ['$set' => $data],
            ['multi' => true, 'upsert' => false]
        );
        $result = $this->db->executeBulkWrite("{$this->config['db_name']}.{$collection}", $bulk);

        // 成功的话，返回一个MongoDB\Driver\WriteResult实例。
        if (get_class($result) == 'MongoDB\Driver\WriteResult') {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 删除数据
     * @param string $collection 集合名称
     * @param array $filter 查询条件
     * @return bool
     * @anthor ljh
     */
    public function delete($collection, $filter)
    {
        $bulk = new BulkWrite();

        $bulk->delete($filter, ['multi' => true, 'upsert' => false]);
        $result = $this->db->executeBulkWrite("{$this->config['db_name']}.{$collection}", $bulk);

        // 成功的话，返回一个MongoDB\Driver\WriteResult实例。
        if (get_class($result) == 'MongoDB\Driver\WriteResult') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 查询数据
     * @param string $collection 集合名称
     * @param array $filter 查询条件
     * @param array $options
     * @return array
     * @anthor ljh
     */
    public function select($collection, $filter, $options = [])
    {
        $query  = new Query($filter, $options);
        $cursor = $this->db->executeQuery("{$this->config['db_name']}.{$collection}", $query);

        $list = [];
        foreach ($cursor as $val) {
            $list[] = (array) $val;
        }
        return $list;
    }

    /**
     * 统计数据
     * @param string $collection 集合名称
     * @param array $filter
     * @return int
     * @anthor ljh
     */
    public function count($collection, $filter = [])
    {
        $command = new Command(['count' => $collection, 'query' => $filter]);
        $result  = $this->db->executeCommand($this->config['db_name'], $command);
        $res     = $result->toArray();

        $num = 0;
        if ($res) {
            $num = $res[0]->n;
        }
        return $num;
    }

    /**
     * 删除数据
     * @param string $collection 集合名称
     * @return bool
     * @anthor ljh
     */
    public function deleteCollection($collection)
    {
        try {
            $result = $this->db->executeCommand($this->config['db_name'], new Command(["drop" => $collection]));

            // 成功的话，返回一个MongoDB\Driver\WriteResult实例。
            if (get_class($result) == 'MongoDB\Driver\Cursor') {
                return true;
            } else {
                return false;
            }
        } catch (CommandException $e) {
            return false;
        }
    }

    // 聚合查询
    public function aggregate($collection, $filter=[], $group=[], $sort=[], $limit=0)
    {
        $cmd = [
            'aggregate' => $collection,
            'pipeline' => [
//                ['$match' => $filter],
//                ['$group' => $group],
//                ['$sort' => $sort],
//                ['$limit' => $limit],
            ]
        ];

        if ($filter) {
            $cmd['pipeline'][] = ['$match' => $filter];
        }
        if ($group) {
            $cmd['pipeline'][] = ['$group' => $group];
        }
        if ($sort) {
            $cmd['pipeline'][] = ['$sort' => $sort];
        }
        if ($limit) {
            $cmd['pipeline'][] = ['$limit' => $limit];
        }

        $command  = new Command($cmd);
        $cursor   = $this->db->executeCommand($this->config['db_name'], $command)->toArray();


        $list = $cursor[0]->result;

        return $list;
    }
}
