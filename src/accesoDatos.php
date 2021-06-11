<?php

class AccesoDatos
{
    private static $_objetoAccesoDatos;
    private $_objetoPDO;

    private function __construct()
    {
        try {
 
            $usuario = "root";
            $clave = "";

            $this->_objetoPDO = new PDO('mysql:host=localhost;dbname=usuarios_bd;charset=utf8', $usuario, $clave);
 
        } catch (PDOException $e) {
 
            print "Error<br/>" . $e->getMessage();
 
            die();
        }
    }

    public static function NuevoObjetoAcceso()
    {
        if (!isset(self::$_objetoAccesoDatos)) {       
            self::$_objetoAccesoDatos = new AccesoDatos(); 
        }
 
        return self::$_objetoAccesoDatos;        
    }

    public function RetornarConsulta($sql)
    {
        return $this->_objetoPDO->prepare($sql);
    }
}


?>