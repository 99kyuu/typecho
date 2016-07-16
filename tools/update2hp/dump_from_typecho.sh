#!/bin/bash
db_name="typechodev"
db_user="root"
db_pass="xinzhong"

tb_content="te_contents"
tb_meta="te_metas"
tb_relationships="te_relationships"

#db_name="your_db_name"
#db_user="your_db_login_user"
#db_pass="your_db_login_pass"
#
#tb_content="typecho_contents"
#tb_meta="typecho_metas"
#tb_relationships="typecho_relationships"

file_content="te_contents.txt"
file_content_ext="te_contents_extend.txt"
file_meta="te_metas.txt"
file_relationship="te_relationships.txt"

split_char="#||#"

function die(){
    test -z "$1" || echo "$1"
    exit 1
}

echo "Dumping content table...."
sql=$(printf 'select `cid`,`title`,`slug`,`created`,`modified`,`order`,`authorId`, `template`,`type`,`status`,`password`,`commentsNum`,`allowComment`,`allowPing`,`allowFeed`,`parent`,"001" into outfile "%s" FIELDS TERMINATED BY "%s" LINES TERMINATED BY "#R#N" from %s' "$file_content" "$split_char" "$tb_content")
#echo $sql && die
mysql -u$db_user -p$db_pass $db_name -e "$sql" || die "Failed to dump data from ${tb_content}"


sql=$(printf 'select cid,title,text into outfile "%s" FIELDS TERMINATED BY "%s"  LINES TERMINATED BY  "#R#N"  from %s' "$file_content_ext" "$split_char"  "$tb_content")
#echo $sql && die
mysql -u$db_user -p$db_pass $db_name -e "$sql" || die "Failed to dump ext data from ${tb_content}"

echo "Dumping meta table..."
sql=$(printf 'select * into outfile "%s" FIELDS TERMINATED BY "%s"  LINES TERMINATED BY  "#R#N"  from %s' "$file_meta" "$split_char" "$tb_meta")
mysql -u$db_user -p$db_pass $db_name -e "$sql" || die "Failed to dump data from ${tb_meta}"

echo "Dumping relationship table..."
sql=$(printf 'select * into outfile "%s" FIELDS TERMINATED BY "%s"  LINES TERMINATED BY  "#R#N"  from %s' "$file_relationship" "$split_char" "$tb_relationships")
mysql -u$db_user -p$db_pass $db_name -e "$sql" || die "Failed to dump data from ${tb_relationships}"

echo "Done for dumping data into files"
echo "下一步:请从数据库目录将导出的txt文件拷贝出来,然后使用load_into_hpte.sh脚本导入数据到HOtypecho数据库中"
echo "注意: 使用load_into_hete.sh脚本导入数据后,需要使用fix_categories.py脚本进行冗余数据修复"