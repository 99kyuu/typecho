#!/usr/bin/env python
# -*- mode: python; coding: utf-8 -*-
import sys,re
reload(sys)
sys.setdefaultencoding('utf-8')
import random,re,time
# ========================
num_categories = 100
num_posts = 500000
split_char = '#||#'              # 一般情况下不需要修改

input_content_seed = "./content.txt"
out_content_file = "./out/te_contents.txt"
out_content_extend_file = "./out/te_contents_extend.txt"
out_meta_file = "./out/te_metas.txt"
out_relationship_file = "./out/te_relationships.txt"
# =========================

#基础函数
def _build_content_line(cat_id,cid,title,content,is_extend):
    global  split_char
    #将特殊的分隔符去掉，避免出现混乱
    content = content.replace(split_char,"*")

    #替换换行符，避免mysql解释错误
    title = title.replace("\r\n","") #title中的换行符直接去掉
    title = title.replace("\n","")
    content = content.replace("\r\n","<br/>") #content中的换成<br/>
    content = content.replace("\n","<br/>")
    #替换所有空格
    slug , _ = re.subn(r"\s+", '_', title)

    #增加替换影响url的特殊字符
    slug=slug.replace('/','-')
    slug=slug.replace('?','-')
    slug=slug.replace('#','-')
    slug=slug.replace('&','-')
    #slug拼接一个cid，避免重复
    slug = "%d_%s" %(cid,slug)

    #组装content内容
    arr_contents = []
    arr_contents.append(str(cid)) #cid
    
    if is_extend:
        arr_contents.append(title) #title
        arr_contents.append(content) # text
    else:
        arr_contents.append(title) #title #冗余字段
        arr_contents.append(slug) #slug
        arr_contents.append(str(int(time.time() + cid))) #created，避免created重复，导致TE的上一篇/下一篇文章错乱
        arr_contents.append(str(int(time.time() + cid))) #modified
        arr_contents.append('0') #order
        arr_contents.append(str(1)) #authorId
        arr_contents.append('\N') #template
        arr_contents.append('post') # type
        arr_contents.append('publish') #status
        arr_contents.append('\N') #password
        arr_contents.append('0') # commentsNum
        arr_contents.append('1') # allowComment
        arr_contents.append('1') # allowPing
        arr_contents.append('1') # allowFeed
        arr_contents.append('0') # parent
        arr_contents.append("%04d" % cat_id)

    row_content = split_char.join(arr_contents)
    return row_content


def _build_relationship(cid,cat_id):
    global split_char
    return  str(cid) + split_char + str(cat_id)

def _build_meta(cat_id,parent_id,count):
    global split_char
    arr_metas = []

    arr_metas.append(str(cat_id))
    arr_metas.append("name_of_category_%d" % cat_id)
    arr_metas.append("slug_of_category_%d" % cat_id)
    arr_metas.append("category")
    arr_metas.append("desc_of_category_%d" % cat_id)
    arr_metas.append(str(count))
    arr_metas.append("1")
    arr_metas.append(str(parent_id))

    return split_char.join(arr_metas)

def _load_content_seed_from_file(input_content_seed):
    #读取文件
    file_object = open(input_content_seed,'r')
    post_content_seed = ""
    try:
         post_content_seed = file_object.read().decode('utf8')
    finally:
         file_object.close()
         return post_content_seed

#创建输出目录
import os
if not os.path.exists('out'): os.mkdir("out")

#目标文件
output_content = open(out_content_file,'w')
output_content_ext = open(out_content_extend_file,'w')
output_relationship = open(out_relationship_file,'w')
output_meta = open(out_meta_file,'w')
#开始生成
post_content_seed = _load_content_seed_from_file(input_content_seed)


#先生成分类
categories = [x + 1 for x in range(num_categories)]
categorie_countor = {}


post_id = 0
last_process = 0
for i_post in range(1,num_posts+1):
    try:
        post_id += 1

        #随机mid
        category_id = random.randint(1,num_categories) + 1

        

        #记录对应关系
        if category_id in categorie_countor:
            count = categorie_countor[category_id] + 1
        else:
            count = 0
        #重新记录回去
        categorie_countor[category_id] = count

        #随机title
        rand_pos = random.randint(0,len(post_content_seed)-100)
        post_title = post_content_seed[rand_pos:rand_pos+50]
        post_title = re.sub(r'<.*?>','',post_title)
        post_title = post_title.replace('<','')
        post_title = post_title.replace('>','')


        #生成随机的字符串
        rand_pos = random.randint(0,len(post_content_seed))
        content = post_content_seed[0:rand_pos]

        arr_contents = content.split(' ')
        random.shuffle(arr_contents)
        content = " ".join(arr_contents)

        #写文件
        line_conent = _build_content_line(category_id,post_id,post_title,content,False)
        line_conent_ext = _build_content_line(category_id,post_id,post_title,content,True)
        line_relationship = _build_relationship(post_id,category_id)

        output_content.write("%s\n" % line_conent)
        output_content_ext.write("%s\n" % line_conent_ext)
        output_relationship.write("%s\n" % line_relationship)

        #计算进度
        s_process = "%0.4f" % (float(post_id) / (num_posts))
        process = float(s_process) * 100
        if process != last_process:
            last_process = process
            msg = "Done for %d%%" % process
            sys.stdout.write(msg + "\r")
    except Exception as e:
        print e
        continue

#写metas
for meta_id in categories:
    #制造随机二级分类
    parent_id = 0
    if(meta_id > num_categories /2):
        parent_id = random.randint(0,num_categories / 2) + 1

    if meta_id in categorie_countor:
        count = categorie_countor.get(meta_id)
    else:
        count = 0

    line_meta = _build_meta(meta_id,parent_id,count)
    output_meta.write("%s\n" % line_meta)

output_meta.flush()
output_content.flush()
output_content_ext.flush()
output_relationship.flush()

output_meta.close()
output_content.close()
output_content_ext.close()
output_relationship.close()

print("Example:LOAD DATA INFILE \"te_content.txt\" into table tb_contents FIELDS  TERMINATED BY '%s';" % split_char)
print("Or, copy out/*.txt files into dataase dir and run load_into_mysql.sh.")




