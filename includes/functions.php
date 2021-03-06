<?php

function example_autoloader($class) {
    if (strpos($class, 'Zend\\') === 0) {
        include (defined('ZF2_PATH') ? ZF2_PATH : __DIR__ . '/../../') . 'library/' . str_replace('\\', '/', $class) . '.php';
    }
}

function assert_example_works($expression, $continue_if_true = false) {
    if ($expression) {
        if ($continue_if_true) {
            return;
        } else {
            echo 'It works!' . PHP_EOL;
            exit(0);
        }
    } else {
        echo 'It DOES NOT work!' . PHP_EOL;
        exit(0);
    }
}

/**
 * @param Zend\Db\Adapter $adapter
 */
function refresh_data($adapter) {
    $platform = $adapter->getPlatform()->getName();
    $vendorData = include __DIR__ . '/../setup/vendor/' . strtolower($platform) . '.php';

    try {
        foreach ($vendorData['data_down'] as $downSql) {
            $adapter->query(
                $downSql,
                $adapter::QUERY_MODE_EXECUTE
            );
        }

        foreach ($vendorData['data_up'] as $tableName => $tableData) {

            if ($tableData == null) {
                continue;
            }

            if (is_int($tableName) && is_string($tableData)) {
                $adapter->query(
                    $tableData,
                    $adapter::QUERY_MODE_EXECUTE
                );
                continue;
            }

            foreach ($tableData as $rowName => $rowData) {

                $keys = array_keys($rowData);
                $values = array_values($rowData);
                array_walk(
                    $values,
                    function (&$value) {
                        $value = ($value == null) ? 'NULL' : '\'' . $value . '\'';
                    }
                );

                $insertSql = 'INSERT INTO ' . $tableName
                    . ' (' . implode(', ', $keys) . ') '
                    . ' VALUES (' .
                        implode(', ',
                            $values
                        )
                    . ');';

                $adapter->query(
                    $insertSql,
                    $adapter::QUERY_MODE_EXECUTE
                );
            }
        }
    } catch (\Exception $e) {
        echo $e->getMessage();
        var_dump($e);
        exit(1);
    }

}