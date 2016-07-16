#!/bin/bash
#Step 0.脚本配置
###Config Here####
db_user='root'
db_passwd='your_password'

typecho_database='typecho'
typecho_database_dir='/var/lib/mysql/typecho/'
typecho_table_prex='typecho_'

hptypecho_database='hpte'
hptypecho_database_dir='/var/lib/mysql/hptypecho/'
hptypecho_table_prex="typecho_"

###Cinfig End#####
#### Constants #############
SPLIT_CHARS="#||#"
############################

function die(){
    test -z $1 || echo $1
    exit 1
}

function e_mysql(){
    test -z "$1" && die "数据库未指定"
    test -z "$2" && die "执行语句未指定"

    db_name="$1"
    db_sql="$2"

    mysql -u$db_user -p$db_passwd "$db_name" "$db_sql"
}


#Step 1.检查环境，通过版本或者检查表结构的方式
e_mysql "$hptypecho_database" "show databases" | grep "$hptypecho_database" || die "未发现HPTypecho数据库"

#Step 2.将原始数据从typecho数据中导出
cd $typecho_database_dir || die "未能成功进入typecho数据库目录,请确认目录配置或者目录权限"
e_mysql "$typecho_database" "SELECT a,b,a+b INTO OUTFILE '$typecho_database_dir/te_contents.txt' FIELDS TERMINATED BY '${SPLIT_CHARS}'  "

#Step 3.将数据库的数据导出到临时目录，select into outfile的形式

#Step 4.将数据导入到临时数据库中

#Step 5.再通过一个临时数据库来切换原数据库和临时数据库