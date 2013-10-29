<?php

namespace Framework
{
    use Framework\Base as Base;
    use Framework\Database as Database;
    use Framework\Database\Exception as Exception;
    
    class Database extends Base
    {
        /**
        * @readwrite
        */
        protected $_type;
        
        /**
        * @readwrite
        */
        protected $_options;
        
        protected function _getExceptionForImplementation($method)
        {
            return new Exception\Implementation("Metoda {$method} nie jest zaimplementowana");
        }
        
        public function initialize()
        {
            if (!$this->type)
            {
                throw new Exception\Argument("Nieprawidłowy typ");
            }
            
            switch ($this->type)
            {
                case "mysql":
                {
                    return new Database\Connector\Mysql($this->options);
                    break;
                }
                default:
                {
                    throw new Exception\Argument("Nieprawidłowy typ");
                    break;
                }
            }
        }
    }
}

namespace Framework\Database
{
    use Framework\Base as Base;
    use Framework\Database\Exception as Exception;
    
    class Connector extends Base
    {
        public function initialize()
        {
            return $this;
        }
        
        protected function _getExceptionForImplementation($method)
        {
            return new Exception\Implementation("Metoda {$method} nie jest zaimplementowana");
        }
    }
}

namespace Framework\Database\Connector
{
    use Framework\Database as Database;
    use Framework\Database\Exception as Exception;
    
    class Mysql extends Database\Connector
    {
        protected $_service;
        
        /**
        * @readwrite
        */
        protected $_host;
        
        /**
        * @readwrite
        */
        protected $_username;
        
        /**
        * @readwrite
        */
        protected $_password;
        
        /**
        * @readwrite
        */
        protected $_schema;
        
        /**
        * @readwrite
        */
        protected $_port = "3306";
        
        /**
        * @readwrite
        */
        protected $_charset = "utf8";
        
        /**
        * @readwrite
        */
        protected $_engine = "InnoDB";
        
        /**
        * @readwrite
        */
        protected $_isConnected = false;
        
        // Sprawdzenie czy jest połączenie z bazą danych
        protected function _isValidService()
        {
            $isEmpty = empty($this->_service);
            $isInstance = $this->_service instanceof \MySQLi;
            
            if ($this->isConnected && $isInstance && !$isEmpty)
            {
                return true;
            }
            
            return false;
        }
        
        // Łączenie z bazą danych
        public function connect()
        {
            if (!$this->_isValidService())
            {
                $this->_service = new \MySQLi(
                    $this->_host,
                    $this->_username,
                    $this->_password,
                    $this->_schema,
                    $this->_port
                );
                
                if ($this->_service->connect_error)
                {
                    throw new Exception\Service("Nie można połączyć się z usługą");
                }
                
                $this->isConnected = true;
            }
            
            return $this;
        }
        
        // Odłączanie od bazy danych
        public function disconnect()
        {
            if ($this->_isValidService())
            {
                $this->isConnected = false;
                $this->_service->close();
            }
            
            return $this;
        }
        
        // Zwraca odpowiedni egzemplarz zapytania
        public function query()
        {
            return new Database\Query\Mysql(array(
                "connector" => $this
            ));
        }
        
        // Wykonuje otrzymaną instrukcję SQL
        public function execute($sql)
        {
            if (!$this->_isValidService())
            {
                throw new Exception\Service("Nie połączono z poprawną usługą");
            }
            
            return $this->_service->query($sql);
        }
        
        // Przystosowuje otrzymaną wartość, aby była bezpieczna dla zapytań
        public function escape($value)
        {
            if (!$this->_isValidService())
            {
                throw new Exception\Service("Nie połączono z poprawną usługą");
            }
            
            return $this->_service->real_escape_string($value);
        }
        
        // Zwraca identyfikator ostatniego wiersza,
        // który ma zostać wstawiony
        public function getLastInsertId()
        {
            if (!$this->_isValidService())
            {
                throw new Exception\Service("Nie połączono z poprawną usługą");
            }
            
            return $this->_service->insert_id;
        }
        
        // Zwraca liczbę wierszy zmienionych
        // przez ostatnie zapytanie SQL
        public function getAffectedRows()
        {
            if (!$this->_isValidService())
            {
                throw new Exception\Service("Nie połączono z poprawną usługą");
            }
            
            return $this->_service->affected_rows;
        }
        
        // Zwraca ostatni błąd
        public function getLastError()
        {
            if (!$this->_isValidService())
            {
                throw new Exception\Service("Nie połączono z poprawną usługą");
            }
            
            return $this->_service->error;
        }
    }
}

namespace Framework\Database
{
    use Framework\Base as Base;
    use Framework\ArrayMethods as ArrayMethods;
    use Framework\Database\Exception as Exception;
    
    class Query extends Base
    {
        /**
        * @readwrite
        */
        protected $_connector;
        
        /**
        * @read
        */
        protected $_from;
        
        /**
        * @read
        */
        protected $_fields;
        
        /**
        * @read
        */
        protected $_limit;
        
        /**
        * @read
        */
        protected $_offset;
        
        /**
        * @read
        */
        protected $_order;
        
        /**
        * @read
        */
        protected $_direction;
        
        /**
        * @read
        */
        protected $_join = array();
        
        /**
        * @read
        */
        protected $_where = array();
        
        protected function _getExceptionForImplementation($method)
        {
            return new Exception\Implementation("Metoda {$method} nie jest zaimplementowana");
        }
                    
        protected function _quote($value)
        {
            if (is_string($value))
            {
                $escaped = $this->connector->escape($value);
                return "'{$escaped}'";
            }
            
            if (is_array($value))
            {
                $buffer = array();
                
                foreach ($value as $i)
                {
                    array_push($buffer, $this->_quote($i));
                }
        
                $buffer = join(", ", $buffer);
                return "({$buffer})";
            }
            
            if (is_null($value))
            {
                return "NULL";
            }
            
            if (is_bool($value))
            {
                return (int) $value;
            }
            
            return $this->connector->escape($value);
        }
        
        protected function _buildSelect()
        {
            $fields = array();
            $where = $order = $limit = $join = "";
            $template = "SELECT %s FROM %s %s %s %s %s";
            
            foreach ($this->fields as $table => $_fields)
            {
                foreach ($_fields as $field => $alias)
                {
                    if (is_string($field))
                    {
                        $fields[] = "{$field} AS {$alias}";
                    }
                    else
                    {
                        $fields[] = $alias;
                    }
                }
            }
            
            $fields = join(", ", $fields);
            
            $_join = $this->join;
            if (!empty($_join))
            {
                $join = join(" ", $_join);
            }
            
            $_where = $this->where;
            if (!empty($_where))
            {
                $joined = join(" AND ", $_where);
                $where = "WHERE {$joined}";
            }
            
            $_order = $this->order;
            if (!empty($_order))
            {
                $_direction = $this->direction;
                $order = "ORDER BY {$_order} {$_direction}";
            }
            
            $_limit = $this->limit;
            if (!empty($_limit))
            {
                $_offset = $this->offset;
                
                if ($_offset)
                {
                    $limit = "LIMIT {$_limit}, {$_offset}";
                }
                else
                {
                    $limit = "LIMIT {$_limit}";
                }
            }
            
            return sprintf($template, $fields, $this->from, $join, $where, $order, $limit);
        }
        
        protected function _buildInsert($data)
        {
            $fields = array();
            $values = array();
            $template = "INSERT INTO `%s` (`%s`) VALUES (%s)";
            
            foreach ($data as $field => $value)
            {
                $fields[] = $field;
                $values[] = $this->_quote($value);
            }
            
            $fields = join("`, `", $fields);
            $values = join(", ", $values);
            
            return sprintf($template, $this->from, $fields, $values);
        }
        
        protected function _buildUpdate($data)
        {
            $parts = array();
            $where = $limit = "";
            $template = "UPDATE %s SET %s %s %s";
            
            foreach ($data as $field => $value)
            {
                $parts[] = "{$field} = ".$this->_quote($value);
            }
            
            $parts = join(", ", $parts);
            
            $_where = $this->where;
            if (!empty($_where))
            {
                $joined = join(", ", $_where);
                $where = "WHERE {$joined}";
            }
            
            $_limit = $this->limit;
            if (!empty($_limit))
            {
                $_offset = $this->offset;
                $limit = "LIMIT {$_limit} {$_offset}";
            }
            
            return sprintf($template, $this->from, $parts, $where, $limit);
        }
        
        protected function _buildDelete()
        {
            $where = $limit ="";
            $template = "DELETE FROM %s %s %s";
            
            $_where = $this->where;
            if (!empty($_where))
            {
                $joined = join(", ", $_where);
                $where = "WHERE {$joined}";
            }
            
            $_limit = $this->limit;
            if (!empty($_limit))
            {
                $_offset = $this->offset;
                $limit = "LIMIT {$_limit} {$_offset}";
            }
            
            return sprintf($template, $this->from, $where, $limit);
        }
        
        public function save($data)
        {
            $isInsert = sizeof($this->_where) == 0;
        
            if ($isInsert)
            {
                $sql = $this->_buildInsert($data);
            }
            else
            {
                $sql = $this->_buildUpdate($data);
            }
            
            $result = $this->_connector->execute($sql);
            
            if ($result === false)
            {
                throw new Exception\Sql();
            }
            
            if ($isInsert)
            {
                return $this->_connector->lastInsertId;
            }
            
            return 0;
        }
        
        public function delete()
        {
            $sql = $this->_buildDelete();
            $result = $this->_connector->execute($sql);
            
            if ($result === false)
            {
                throw new Exception\Sql();
            }
            
            return $this->_connector->affectedRows;
        }
        
        public function from($from, $fields = array("*"))
        {
            if (empty($from))
            {
                throw new Exception\Argument("Nieprawidłowy argument");
            }
            
            $this->_from = $from;
            
            if ($fields)
            {
                $this->_fields[$from] = $fields;
            }
            
            return $this;
        }
        
        public function join($join, $on, $fields = array())
        {
            if (empty($join))
            {
                throw new Exception\Argument("Nieprawidłowy argument");
            }
            
            if (empty($on))
            {
                throw new Exception\Argument("Nieprawidłowy argument");
            }
            
            $this->_fields += array($join => $fields);
            $this->_join[] = "JOIN {$join} ON {$on}";
            
            return $this;
        }
        
        public function limit($limit, $page = 1)
        {
            if (empty($limit))
            {
                throw new Exception\Argument("Nieprawidłowy argument");
            }
            
            $this->_limit = $limit;
            $this->_offset = $limit * ($page - 1);
            
            return $this;
        }
        
        public function order($order, $direction = "asc")
        {
            if (empty($order))
            {
                throw new Exception\Argument("Nieprawidłowy argument");
            }
            
            $this->_order = $order;
            $this->_direction = $direction;
            
            return $this;
        }
        
        public function where()
        {
            $arguments = func_get_args();
            
            if (sizeof($arguments) < 1)
            {
                throw new Exception\Argument("Nieprawidłowy argument");
            }
            
            $arguments[0] = preg_replace("#\?#", "%s", $arguments[0]);
            
            foreach (array_slice($arguments, 1, null, true) as $i => $parameter)
            {
                $arguments[$i] = $this->_quote($arguments[$i]);
            }
            
            $this->_where[] = call_user_func_array("sprintf", $arguments);
            
            return $this;
        }
        
        public function first()
        {
            $limit = $this->_limit;
            $offset = $this->_offset;
            
            $this->limit(1);
            
            $all = $this->all();
            $first = ArrayMethods::first($all);
        
            if ($limit)
            {
                $this->_limit = $limit;
            }
            if ($offset)
            {
                $this->_offset = $offset;
            }
            
            return $first;
        }
        
        public function count()
        {
            $limit = $this->limit;
            $offset = $this->offset;
            $fields = $this->fields;
            
            $this->_fields = array($this->from => array("COUNT(1)" => "rows"));
            
            $this->limit(1);
            $row = $this->first();
            
            $this->_fields = $fields;
            
            if ($fields)
            {
                $this->_fields = $fields;
            }
            if ($limit)
            {
                $this->_limit = $limit;
            }
            if ($offset)
            {
                $this->_offset = $offset;
            }
            
            return $row["rows"];
        }
    }
}

namespace Framework\Database\Query
{
    use Framework\Database as Database;
    use Framework\Database\Exception as Exception;
    
    class Mysql extends Database\Query
    {
        public function all()
        {
            $sql = $this->_buildSelect();
            $result = $this->connector->execute($sql);
            
            if ($result === false)
            {
                $error = $this->connector->lastError;
                throw new Exception\Sql("Wystąpił błąd w zapytaniu SQL: {$error}");
            }
            
            $rows = array();
            
            for ($i = 0; $i < $result->num_rows; $i++)
            {
                $rows[] = $result->fetch_array(MYSQLI_ASSOC);
            }
            
            return $rows;
        }
    }
}

