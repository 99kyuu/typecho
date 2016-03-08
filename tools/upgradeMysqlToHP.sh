#!/bin/bash
#Step 1.检查环境，通过版本或者检查表结构的方式

#Step 2.根据install下的MySQL.sql创建标记结构，使用临时的数据库名

#Step 3.将数据库的数据导出到临时目录，select into outfile的形式

#Step 4.将数据导入到临时数据库中

#Step 5.再通过一个临时数据库来切换原数据库和临时数据库