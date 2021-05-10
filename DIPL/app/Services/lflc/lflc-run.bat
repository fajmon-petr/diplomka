@echo off

::
:: Environment settings
::
set dir=%~d0%~p0
set lflc=%dir%\hierarchic_base.exe

::
:: SGS LFLC default files
::

:: LFLC knowledge base file
set kbn=test_db.knb

:: LFLC input file
set input=data.txt

:: LFLC output file
set output=output.txt


::
:: LFLC execution
::
@echo on
%lflc% -k %dir%\%kbn% -i %dir%\%input% -o %dir%\%output%
