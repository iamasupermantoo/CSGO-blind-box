<?php


namespace app\admin\controller;


use think\Db;

class Lucky
{
    //幸运饰品列表
    public function skinList(){
        $page        = input('post.page',1);
        $pageSize    = input('post.pageSize',10);
        $recommend   = input('post.recommend');             //是否为推荐1：是，2：否
        $new         = input('post.new');                   //是否为新品1：是，2：否
        $type_id     = input('post.type_id');
        $subclass_id = input('post.subclass_id');

        $newW         = $new ?           ['s.new' => $new] : '';
        $recommendW   = $recommend ?     ['s.recommend' => $recommend] : '';
        $type_idW     = ($type_id > 2) ? ['s.type_id' => $type_id]  : '';
        $subclass_idW = $subclass_id ?   ['s.subclass_id'=>$subclass_id] : '';

        $list = Db::table('all_skin')
            ->alias('as')
            ->join('lucky_skin s', 's.skin_id = as.id', 'left')
            ->join('lucky_skin_type st', 'st.id = s.type_id', 'left')
            ->join('lucky_skin_subclass sts', 'sts.id = s.subclass_id', 'left')
            ->limit(($page - 1) * $pageSize, $pageSize)
            ->field('as.*,s.type_id,st.name as type_name,s.subclass_id,sts.name as subclass_name')
            ->where($newW)
            ->where($recommendW)
            ->where($type_idW)
            ->where($subclass_idW)
            ->where(['flag'=>1])
            ->select();
        $total = Db::table('all_skin')
            ->alias('as')
            ->join('lucky_skin s', 's.skin_id = as.id', 'left')
            ->where(['flag'=>1])
            ->where($newW)
            ->where($recommendW)
            ->where($type_idW)
            ->where($subclass_idW)
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }


    //幸运饰品分类列表
    public function skinType(){
        $list = Db::table('lucky_skin_type')
            ->where(['flag'=>1])
            ->order('order','asc')
            ->select();
        $total = Db::table('lucky_skin_type')
            ->where(['flag'=>1])
            ->count();
        if($total>0){
            foreach ($list as $k=>$v){
                $list[$k]['img'] = $list[$k]['img'] ? mainName().$list[$k]['img'] : '';
            }
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
    }

    //饰品小类
    public function typeSubclass(){
        $skin_type_id = input('post.skin_type_id');
        $list = Db::table('lucky_skin_subclass')
            ->field('id,name as subclass_name,skin_type_id as type_id')
            ->where(['flag'=>1,'skin_type_id'=>$skin_type_id])
            ->select();
        $total = Db::table('lucky_skin_subclass')
            ->where(['flag'=>1,'skin_type_id'=>$skin_type_id])
            ->count();
        if($total>0){
            $res = ['total'=>$total,'list'=>$list];
            return result(1,$res,'');
        }else{
            return result(0,'','无数据');
        }
        return $list;
    }

    //添加幸运皮肤类型
    public function addLucySkinType(){
        $data = json_decode(input('post.')['data'],true);
        $is_insert = json_decode(input('post.')['subclassInfo'],true);
        $lucky_skin_type = [
            'name' => $data['name'],
            'create_time' => currentTime()
        ];
        $file = $_FILES;
        $url = '';
        if($file){
            $upload = New Upload();
            $key = array_keys($file)[0];
            $url = $upload->upload('room',$file,$key);
        }
        if($url){
            $lucky_skin_type['img'] = $url;
        }
        $skin_type_id = Db::table('lucky_skin_type')->insertGetId($lucky_skin_type);
        if($is_insert){
            foreach ($is_insert as $k=>$v){
                $insert['name'] = $is_insert[$k]['subclass_name'];
                $insert['skin_type_id'] = $skin_type_id;
                if(!empty(trim($insert['name']))){
                    Db::table('lucky_skin_subclass')->insert($insert);
                }
            }
        }
        return result(1,'','');
    }

    //编辑幸运皮肤类型
    public function editLucySkinType(){
        $data = json_decode(input('post.')['data'],true);
        $id = $data['id'];
        $is_insert = json_decode(input('post.')['subclassInfo'],true);
        Db::startTrans();
        try {
            if($is_insert){
                $is_delete = Db::table('lucky_skin_subclass')->where('skin_type_id',$id)->select();
                //循环中已操作更新，只记录下需要删除和新增的数据即可
                $ins = [];
                foreach ($is_insert as $k=>$v){

                    foreach ($is_delete as $key=>$val){

                        if(($is_insert[$k]['id']>0) && ($is_insert[$k]['id'] == $is_delete[$key]['id'])){
                            //更新,移除被更新数据，剩余的插入数据库
                            if($is_insert[$k]['subclass_name'] !=  $is_delete[$key]['name']){
                                $update['name'] = $is_insert[$k]['subclass_name'];
                                Db::table('lucky_skin_subclass')->where('id', $is_delete[$key]['id'])->update($update);
                                $is_insert[$k]['state'] = 'up';
                            }
                            $is_insert[$k]['state'] = 'lue';
                            unset($is_delete[$key]);
                        }
                    }
                    if(($is_insert[$k]['id'] ==  '')){
                        $is_insert[$k]['state'] = 'in';
                    }
                }

                $is_delete = array_values($is_delete);
                //删除
                if($is_delete){
                    foreach ($is_delete as $k=>$v){
                        $delete_id = $is_delete[$k]['id'];
                        Db::table('lucky_skin_subclass')->where('id', $delete_id)->delete();
                    }
                }
                //插入
                if($is_insert){
                    foreach ($is_insert as $k=>$v){
                        if($is_insert[$k]['state'] == 'in'){
                            $insert['name'] = $is_insert[$k]['subclass_name'];
                            $insert['skin_type_id'] = $is_insert[$k]['type_id'];
                            if(!empty(trim($insert['name']))){
                                Db::table('lucky_skin_subclass')->insert($insert);
                            }
                        }
                    }
                }
            }else{
                Db::table('lucky_skin_subclass')->where('skin_type_id',$id)->delete();
            }
            $lucky_skin_type = [
                'name'  => $data['name'],
                'order' => $data['order'],
//                'create_time' => currentTime()
            ];
            $type = Db::table('lucky_skin_type')->where('id',$id)->find();
            if(($type['status'] == 2) && ($type['name'] != $data['name'])){
                return result(0,'','当前类型名称：【'.$type['name'].'】不可操作修改或删除');
            }
            if(($type['status'] == 2) && ($data['order'] != 1)){
                return result(0,'','当前类型名称：【'.$type['name'].'】不可操作修改或删除');
            }
            if(($type['status'] < 2 ) && $data['order'] <= 1){
                return result(0,'','当前排序不可用');
            }
            $file = $_FILES;
            $url = '';
            if($file){
                $upload = New Upload();
                $key = array_keys($file)[0];
                $url = $upload->upload('room',$file,$key);
            }
            if($url){
                $lucky_skin_type['img'] = $url;
            }
            Db::table('lucky_skin_type')->where('id',$id)->update($lucky_skin_type);
            Db::commit();
            return result(1,'','');
        }catch (\Exception $e){
            Db::rollback();
            return result(1,'',$e->getMessage());
        }

    }

    //
    public function del(){
        $id = input('post.id');
        $type = Db::table('lucky_skin_type')->where('id',$id)->find();
        if($type['status'] == 2){
            return result(0,'','当前类型名称：【'.$type['name'].'】不可操作修改或删除');
        }
        Db::table('lucky_skin_type')->where('id',$id)->delete();
        return result(1,'','');
    }

}