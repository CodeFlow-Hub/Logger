<?php

use CodeFlowHub\Logger\Logger;

if (!function_exists('logger'))
{
   /**
    * Função helper global para acessar o Logger de forma simplificada
    *
    * @return Logger
    */
   function logger(): Logger
   {
      return new Logger();
   }
}
