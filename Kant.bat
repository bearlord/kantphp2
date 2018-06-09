@echo off

rem -------------------------------------------------------------
rem  KantPHP command line bootstrap script for Windows.
rem
rem  @author Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
rem  @author Qiang Xue <qiang.xue@gmail.com>
rem  @link http://www.kantphp.com/
rem  @copyright Copyright (c) 2008 KantPHP
rem  @license http://www.kantphp.com/license/
rem -------------------------------------------------------------

@setlocal

set KANT_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe

"%PHP_COMMAND%" "%KANT_PATH%Kant" %*

@endlocal
