<?php
/**
 * Created by PhpStorm.
 * User: plotnikov
 * Date: 08.02.2017
 * Time: 10:28
 */


class Errors {
  const ERROR_CORE = 500;
}

class ConfigModuleException extends \Exception {}

/**
 * Class Config
 * @package Core\basic
 *
 * @description
 Читает конфиги вида
    <?php
        return [];
 Производит контроль целостности конфига, его наличия, задает значения по-умолчанию.
 Предоставляет простой объектный стиль доступа или array access к конфигу
 */
class Config implements \ArrayAccess
{
    /**
     * @var array Массив конфигурации
     */
    protected $_configValues = [];

    /**
     * Config constructor.
     * @param $configFilename путь до файла конфигурации
     * @param array $defaultValues массив значений по-умолчанию
     * @param array $requiredParams массив обязательных ключей конфигурации
     * @param bool $isConfigRequired контроль наличия конфига.
     * @throws ConfigModuleException
     */
    public function __construct($configFilename, array $defaultValues = [], array $requiredParams = [], $isConfigRequired = true)
    {
        $this->_configValues = $this->readConfig($configFilename);

        // Подстановка дефолтных значений при их отсутствии
        foreach ($defaultValues as $param => $value) {
            $this->_configValues[$param] = isset($this->_configValues[$param]) ? $this->_configValues[$param] : $value;
        }

        $notFoundRequiredParams = array_diff($requiredParams, array_keys($this->_configValues));
        if (!empty(($notFoundRequiredParams))) {
            throw new ConfigModuleException("Required param(s) \"" . implode(', ', $notFoundRequiredParams) . "\" has not been set.", Errors::ERROR_CORE);
        }
    }

    /**
     * Читает конфиг из файла в массив
     * @param $configFilename
     * @return array
     * @throws ConfigModuleException
     */
    protected function readConfig($configFilename) :array
    {
        if (!file_exists($configFilename) || !is_readable($configFilename)) {
            if ($this->_isConfigRequired) {
                throw new ConfigModuleException("Config file \"{$configFilename}\" not found.", Errors::ERROR_CORE);
            } else {
                return [];
            }
        }

        try {
            $config = require $configFilename;
        } catch (\Exception $ex) {
            throw new ConfigModuleException("Config \"{$configFilename}\" can't be load.", Errors::ERROR_CORE, $ex);
        }

        return (array) $config;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return isset($this->_configValues[$name]) ? $this->_configValues[$name] : null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name) :bool
    {
        return isset($this->_configValues[$name]);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (!empty($name)) {
            $this->_configValues[$name] = $value;
        } else {
            $this->_configValues[] = $value;
        }
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->_configValues[$name]);
    }

    /**
     * @return string
     */
    public function __toString() :string
    {
        return json_encode($this->_configValues, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    # Array access

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }
}
