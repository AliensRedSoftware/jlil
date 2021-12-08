@if "%DEBUG%" == "" @echo off
@rem ##########################################################################
@rem
@rem  Gradle startup script for Windows
@rem
@rem ##########################################################################

@rem Set local scope for the variables with windows NT shell
if "%OS%"=="Windows_NT" setlocal

set DIRNAME=%~dp0
if "%DIRNAME%" == "" set DIRNAME=.
set APP_BASE_NAME=%~n0
set APP_HOME=%DIRNAME%

@rem Add default JVM options here. You can also use JAVA_OPTS to pass JVM options to this script.
set DEFAULT_JVM_OPTS= -Dfile.encoding=UTF-8

@rem Find java.exe

if exist "%APP_HOME%\jre" set JAVA_HOME=%APP_HOME%\jre

if defined JAVA_HOME goto findJavaFromJavaHome

set JAVA_EXE=java.exe
%JAVA_EXE% -version >NUL 2>&1
if "%ERRORLEVEL%" == "0" goto init

echo.
echo ERROR: JAVA_HOME is not set and no 'java' command could be found in your PATH.
echo.
echo Please set the JAVA_HOME variable in your environment to match the
echo location of your Java installation.

goto fail

:findJavaFromJavaHome
set JAVA_HOME=%JAVA_HOME:"=%
set JAVA_EXE=%JAVA_HOME%/bin/java.exe

if exist "%JAVA_EXE%" goto init

echo.
echo ERROR: JAVA_HOME is set to an invalid directory: %JAVA_HOME%
echo.
echo Please set the JAVA_HOME variable in your environment to match the
echo location of your Java installation.

goto fail

:init
@rem Get command-line arguments, handling Windows variants

if not "%OS%" == "Windows_NT" goto win9xME_args

:win9xME_args
@rem Slurp the command line arguments.
set CMD_LINE_ARGS=
set _SKIP=2

:win9xME_args_slurp
if "x%~1" == "x" goto execute

set CMD_LINE_ARGS=%*

goto execute

:execute
@rem Setup the command line

set CLASSPATH=%APP_HOME%\mips-last.jar;%APP_HOME%\libs\slf4j-api-1.6.6.jar;%APP_HOME%\libs\asm-tree-7.3.1.jar;%APP_HOME%\libs\jphp-core-0.9.3-SNAPSHOT.jar;%APP_HOME%\libs\asm-util-7.3.1.jar;%APP_HOME%\libs\jphp-zend-ext-0.9.3-SNAPSHOT.jar;%APP_HOME%\libs\gson-2.7.jar;%APP_HOME%\libs\jphp-json-ext-0.9.3-SNAPSHOT.jar;%APP_HOME%\libs\asm-commons-7.3.1.jar;%APP_HOME%\libs\jphp-xml-ext-0.9.3-SNAPSHOT.jar;%APP_HOME%\libs\x-jphp-gui-ext-3.0.1.jar;%APP_HOME%\libs\jphp-runtime-1.0.3.jar;%APP_HOME%\libs\jphp-graphic-ext-0.9.3-SNAPSHOT.jar;%APP_HOME%\libs\asm-analysis-7.3.1.jar;%APP_HOME%\libs\jphp-zip-ext-0.9.3-SNAPSHOT.jar;%APP_HOME%\libs\asm-7.3.1.jar;%APP_HOME%\libs\zt-zip-1.11.jar

@rem Execute jppm
"%JAVA_EXE%" %DEFAULT_JVM_OPTS% %JAVA_OPTS% -cp "%CLASSPATH%" php.runtime.launcher.Launcher %CMD_LINE_ARGS%

:end
@rem End local scope for the variables with windows NT shell
if "%ERRORLEVEL%"=="0" goto mainEnd

:fail
rem Set variable GRADLE_EXIT_CONSOLE if you need the _script_ return code instead of
rem the _cmd.exe /c_ return code!
if  not "" == "%GRADLE_EXIT_CONSOLE%" exit 1
exit /b 1

:mainEnd
if "%OS%"=="Windows_NT" endlocal

:omega
