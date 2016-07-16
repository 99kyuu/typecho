假如:
源数据库(typecho数据库)为typecho,目录/var/lib/mysql/typecho
目的数据库(hptypecho数据库)为typecho,目录/var/lib/mysql/hptypecho

step 1.运行dump_from_typecho.sh脚本,将数据导出
stpe 2.到/var/lib/mysql/typecho下,找到所有txt文件,复制到/var/lib/mysql/hptypecho
step 3.运行load_into_hpte.sh脚本,将数据从txt中导入到新数据库中
step 4.运行fix_categories.py脚本,修复数据冗余