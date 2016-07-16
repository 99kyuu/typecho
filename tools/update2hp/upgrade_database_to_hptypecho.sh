#!/bin/bash
#Step 0.脚本配置
###Config Here####
db_user='root'  #数据库登陆用户名
db_passwd='your_password' #数据库登陆密码

typecho_database='typecho' #typecho的数据库名
typecho_table_prex='typecho_' #typecho的数据库前缀

hptypecho_database='hpte' #hptypecho数据库名
hptypecho_table_prex="typecho_" #hptyecho数据库前缀
###############################

function die(){
    test -z $1 || echo $1
    exit 1
}

#Step 1.检查环境，通过版本或者检查表结构的方式
mysql -u$db_user -p$db_passwd -e  "show databases" | grep "$hptypecho_database" || die "未发现HPTypecho数据库"

# Step 2.复制数据
echo "Copying content data..."

sql_tmp="insert into %s.%s%s ( \
    cid,title,slug,created,modified,\`order\`,authorId, \
    template,type,status,password,commentsNum,allowComment, \
    allowPing,allowFeed,parent,ext_categories) \
    select cid,title,slug,created,modified,\`order\`,authorId, \
    template,type,status,password,commentsNum,allowComment, \
    allowPing,allowFeed,parent,\"001\" \
    from %s.%s%s"
sql=$(printf "$sql_tmp" "$hptypecho_database" "$hptypecho_table_prex" "contents_source" "$typecho_database" "$typecho_table_prex" "contents")
mysql -u$db_user -p$db_passwd -e "$(printf 'delete from %s.%scontents_source' $hptypecho_database $hptypecho_table_prex)" || die "清理content_source数据失败"
mysql -u$db_user -p$db_passwd -e "$sql" || die "转换content_source失败"

sql_tmp="insert into %s.%s%s ( \
    cid,title,text) \
    select cid,title,text \
    from %s.%s%s"
sql=$(printf "$sql_tmp" "$hptypecho_database" "$hptypecho_table_prex" "contents_extend" "$typecho_database" "$typecho_table_prex" "contents")
mysql -u$db_user -p$db_passwd -e "$(printf 'delete from %s.%scontents_extend' $hptypecho_database $hptypecho_table_prex)" || die "清理contents_extend数据失败"
mysql -u$db_user -p$db_passwd -e "$sql" || die "转换content_extend失败"


sql_tmp="insert into %s.%s%s ( \
    cid,title,text) \
    select cid,title,text \
    from %s.%s%s"
sql=$(printf "$sql_tmp" "$hptypecho_database" "$hptypecho_table_prex" "contents_index" "$typecho_database" "$typecho_table_prex" "contents")
mysql -u$db_user -p$db_passwd -e "$(printf 'delete from %s.%scontents_index' $hptypecho_database $hptypecho_table_prex)" || die "清理contents_index数据失败"
mysql -u$db_user -p$db_passwd -e "$sql" || die "转换content_index失败"

sql_tmp="insert into %s.%s%s select * from %s.%s%s"
sql=$(printf "$sql_tmp" "$hptypecho_database" "$hptypecho_table_prex" "metas" "$typecho_database" "$typecho_table_prex" "metas")
mysql -u$db_user -p$db_passwd -e "$(printf 'delete from %s.%smetas' $hptypecho_database $hptypecho_table_prex)" || die "清理metas数据失败"
mysql -u$db_user -p$db_passwd -e "$sql" || die "转换metas失败"


sql_tmp="insert into %s.%s%s select * from %s.%s%s"
sql=$(printf "$sql_tmp" "$hptypecho_database" "$hptypecho_table_prex" "relationships" "$typecho_database" "$typecho_table_prex" "relationships")
mysql -u$db_user -p$db_passwd -e "$(printf 'delete from %s.%srelationships' $hptypecho_database $hptypecho_table_prex)" || die "清理relationships数据失败"
mysql -u$db_user -p$db_passwd -e "$sql" || die "转换relationships失败"

echo "Done"
