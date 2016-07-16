#!/bin/bash
db_name="your_database"
db_user="your_username"
db_pass="your_password"

tb_content="typecho_contents_source"
tb_content_ext="typecho_contents_extend"
tb_content_index="typecho_contents_index"
tb_meta="typecho_metas"
tb_relationships="typecho_relationships"

file_content="te_contents.txt"
file_content_ext="te_contents_extend.txt"
file_meta="te_metas.txt"
file_relationship="te_relationships.txt"

split_char="#||#"

mysql -u$db_user -p$db_pass $db_name -e "delete from  ${tb_relationships}"
mysql -u$db_user -p$db_pass $db_name -e "delete from  ${tb_meta}"
mysql -u$db_user -p$db_pass $db_name -e "delete from  ${tb_content_ext}"
mysql -u$db_user -p$db_pass $db_name -e "delete from  ${tb_content_index}"
mysql -u$db_user -p$db_pass $db_name -e "delete from  ${tb_content}"

mysql -u$db_user -p$db_pass $db_name -e "LOAD DATA INFILE \"${file_relationship}\" into table ${tb_relationships} FIELDS  TERMINATED BY \"${split_char}\"";
echo "done for ${tb_relationships}"
mysql -u$db_user -p$db_pass $db_name -e "LOAD DATA INFILE \"${file_meta}\" into table ${tb_meta} FIELDS  TERMINATED BY \"${split_char}\"";
echo "done for ${tb_meta}"
mysql -u$db_user -p$db_pass $db_name -e "LOAD DATA INFILE \"${file_content}\" into table ${tb_content} FIELDS  TERMINATED BY \"${split_char}\"";
echo "done for ${tb_content}"
mysql -u$db_user -p$db_pass $db_name -e "LOAD DATA INFILE \"${file_content_ext}\" into table ${tb_content_ext} FIELDS  TERMINATED BY \"${split_char}\"";
echo "done for ${tb_content_ext}"
mysql -u$db_user -p$db_pass $db_name -e "LOAD DATA INFILE \"${file_content_ext}\" into table ${tb_content_index} FIELDS  TERMINATED BY \"${split_char}\"";
echo "done for ${tb_content_index}"

echo "Cleaning txt files..."
rm $file_meta
rm $file_content
rm $file_relationship
rm $file_content_ext
echo "Done"